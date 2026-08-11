# Пояснение по техническим решениям

## Задание

Микросервис уведомлений: массовая рассылка SMS/Email.

## Стек

* PHP 8.4
* Laravel 13
* PostgreSQL
* RabbitMQ
* Redis
* Nginx + PHP-FPM
* Docker Compose
* OpenAPI / Swagger
* PHPUnit

## Основные сущности

* `recipients` — получатели уведомлений
* `notification_batches` — пачки массовой рассылки
* `notifications` — отдельные уведомления конкретным получателям
* `notification_outbox` — outbox-записи для надежной публикации в очередь

## Ключевые решения

### Laravel вместо FastAPI

В задании предлагался PHP/Laravel или Python/FastAPI. Был выбран Laravel, потому что проект выполняется под вакансию со знанием Laravel и лучше демонстрирует релевантный стек.

### RabbitMQ как брокер сообщений

RabbitMQ используется для асинхронной обработки уведомлений. Почему RabbitMQ, а не Kafka: подходит под классическую очередь задач без переусложнений.

### Что складывается в очередь

В RabbitMQ отправляется только `notification_id`, а не весь payload уведомления.

Причины:

* PostgreSQL остается источником истины;
* сообщение в очереди маленькое;
* worker всегда получает актуальное состояние из БД;
* проще менять структуру данных;
* проще защититься от повторной отправки.

### Статусы уведомлений

Используются статусы:

* `pending` — запись создана, но еще не опубликована в очередь;
* `queued` — запись успешно опубликована в очередь;
* `reconciliation_pending` — recovery переотправил job для зависшего `queued`;
* `sent` — уведомление передано провайдеру;
* `delivered` — провайдер подтвердил доставку;
* `failed` — внутренняя ошибка отправки после исчерпания retries job;
* `discarded` — провайдер сообщил об ошибке доставки.

Статус `pending` добавлен дополнительно, чтобы не считать уведомление поставленным в очередь до успешной публикации job.

`failed` и `discarded` разделены намеренно: первый — сбой нашей отправки (например, отсутствует контакт получателя), второй — отказ провайдера после успешной передачи сообщения.

### Recovery

Recovery реализован двумя независимыми механизмами:

#### 1. Outbox recovery (`pending` → `queued`)

Transactional outbox: неопубликованные outbox-записи повторно обрабатываются:

* `PublishNotificationOutboxJob` (очередь `notifications.outbox`);
* scheduler и artisan-команда `notifications:publish-outbox`.

Закрывает разрыв между commit в PostgreSQL и появлением job в RabbitMQ.

#### 2. Reconciliation stuck queued (`queued` → `reconciliation_pending`)

Если уведомление долго остается в `queued` (job потерян из очереди, worker недоступен и т.п.), scheduler и команда `notifications:reconcile-stuck` переотправляют `SendNotificationJob` и переводят запись в `reconciliation_pending`.

Порог «зависания» задается `NOTIFICATION_STUCK_QUEUED_THRESHOLD_MINUTES` (например 5 минут).

Статус `reconciliation_pending` нужен, чтобы scheduler не дублировал job бесконечно (пока штатная работа не восстановится) для одной и той же записи.

`SendNotificationJob` обрабатывает и `queued`, и `reconciliation_pending` (метод `NotificationStatus::isSendable()`).

### Provider Event endpoint

Для имитации подтверждения доставки сообщения от провайдера реализован endpoint:

```http
POST /api/provider-events/delivery-status
```

Он принимает `provider_message_id` и статус доставки.

Это позволяет отдельно показать сценарии:

* `sent → delivered`
* `sent → discarded`

Асинхронная обработка в данном случае не реализована, т.к. здесь важнее было показать само событие и смену статусов.

### Mock-провайдеры

Вместо реальных SMS/Email шлюзов используются классы-заглушки:

* `SmsProviderMock`
* `EmailProviderMock`

Они реализуют общий `NotificationProviderInterface`.

Такой подход позволяет легко заменить mock на реальную интеграцию.

### Очереди RabbitMQ

Используются очереди:

* `notifications.outbox` — relay outbox-записей в рабочие очереди;
* `notifications.critical` — транзакционные уведомления;
* `notifications.default` — маркетинговые уведомления.

Причины:

* явное разделение, проще мониторить;
* изоляция трафика;
* можно задать разные настройки, например retry.

В production можно давать critical больше ресурсов, например:

```text
critical: 3-5 workers
default: 1-2 workers
```

### NotificationType управляет priority и queue

`NotificationType` инкапсулирует бизнес-правила маршрутизации:

* какой priority назначить уведомлению;
* в какую очередь его отправить.

Это упрощает расширение: при добавлении нового типа уведомления меняется enum, а не сервисная логика.

### Idempotency-Key

Для защиты от повторного создания одной и той же массовой рассылки используется заголовок:

```http
Idempotency-Key
```

Ключ хранится в PostgreSQL в таблице `notification_batches`.

Повторный запрос с тем же ключом возвращает уже созданный batch и HTTP `200 OK`, а не создает новые уведомления.

Первичный запрос возвращает `201 Created`.

### Redis: locks и rate limits

Redis используется для быстрых временных ограничений и блокировок.

#### Lock при обработке job

При обработке `SendNotificationJob` используется Redis lock:

```text
notification:{id}
```

Это защищает от ситуации, когда одно и то же уведомление одновременно обрабатывается несколькими worker'ами.

#### Контроль лимитов по API token

Для endpoint'а:

```http
POST /api/notification-batches
```

Если один вызывающий сервис начнет отправлять слишком много batch-запросов, он будет временно ограничен. При этом другие клиенты API смогут продолжить работу. Счетчики rate limit хранятся через Laravel RateLimiter в cache store. Так как cache store настроен на Redis, эти счетчики хранятся в Redis.

В качестве расширения было бы полезно добавить контроль лимитов по провайдеру, получателю, типу уведомления. 

### At-least-once и exactly-once

RabbitMQ обеспечивает семантику at-least-once: сообщение может быть доставлено worker'у больше одного раза.

Чтобы не отправить одно уведомление повторно, используется защита на уровне бизнес-логики:

* Redis lock (`notification:{id}`);
* проверка статуса в БД — job отправляет только уведомления в статусах `queued` и `reconciliation_pending` (`NotificationStatus::isSendable()`);
* идемпотентность outbox relay (`published_at`, проверка `pending`).

### Получатели

Для получателей реализована локальная таблица `recipients` для имитации внешней системы:

* Notification Service получил recipient_ids
* ↓
* сходил бы в Recipient/User/Profile service
* ↓
* получил бы контакты и проверил существование
* ↓
* создал notifications

Для удобства проверки автоматически создаются 10 тестовых получателей.

### История уведомлений

Под историей уведомлений понимается список уведомлений подписчика с текущими статусами.

Полная история смены статусов (`queued → sent → delivered`) отдельно не хранится.

Endpoint:

```http
GET /api/recipients/{recipient}/notifications
```

Возвращает уведомления конкретного получателя с текущими статусами.

### API Token и Provider Token

Клиентское API защищено Bearer token:

```http
Authorization: Bearer test-token
```

Provider event endpoint защищен отдельным заголовком:

```http
X-Provider-Token: super-secret-token
```

В production вместо простого токена можно использовать более сложные варианты защиты.

### DTO

DTO используются, чтобы сервисы не работали с сырыми массивами из request.

Примеры:

* `CreateNotificationBatchData`
* `ProviderDeliveryStatusData`
* `NotificationBatchCreationResult`

### Resources

Resources используются для формирования API-ответов:

* `NotificationBatchResource`
* `NotificationHistoryResource`
* `ProviderEventResource`

Они позволяют явно определить, какие поля увидит клиент, и не смешивать форматирование ответа с логикой контроллеров.

### Почему нет Repository слоя

Repository слой намеренно не добавлен.

Запросы к БД сейчас простые и находятся внутри сервисов. Добавление repository в данном случае создало бы лишнюю абстракцию без реальной пользы.

### Тесты

Добавлены feature/integration tests для основных сценариев.

Тесты используют SQLite in-memory, заданный в `phpunit.xml`.

Это ускоряет запуск и изолирует тестовые данные от основной PostgreSQL базы.

### OpenAPI / Swagger

Документация API доступна через Swagger UI:

```http
http://localhost:8080/api/documentation
```

Главная страница проекта перенаправляет на Swagger UI.
