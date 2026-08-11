<h1>Notification Service</h1>
<p>Микросервис уведомлений: массовая рассылка SMS/Email.</p>

<p>Текст задания: <a href="TASK.md">TASK.md</a></p>

<p>Используемые технологии и технические решения: <a href="TECHNICAL_NOTES.md">TECHNICAL_NOTES.md</a></p>

<br>
<h2>📂 Структура проекта</h2>
<pre>
├── docker/                                      # настройки Docker (PHP, Nginx, init-скрипты)
├── src/
│   ├── app/
│   │   ├── Console/Commands/                   # recovery: publish-outbox, reconcile-stuck
│   │   ├── DTO/                                # DTO для входных данных и результатов сервисов
│   │   ├── Enums/                              # статусы, каналы, типы и приоритеты уведомлений
│   │   ├── Exceptions/                         # доменные и HTTP-aware исключения
│   │   ├── Http/
│   │   │   ├── Controllers/Api/                # API-контроллеры
│   │   │   ├── Middleware/                     # API token, provider token, Idempotency-Key
│   │   │   ├── RateLimiting/                   # rate limit для создания batch
│   │   │   ├── Requests/                       # валидация API-запросов
│   │   │   └── Resources/                      # форматирование API-ответов
│   │   ├── Interfaces/                         # интерфейс провайдера уведомлений
│   │   ├── Jobs/                               # PublishNotificationOutboxJob, SendNotificationJob
│   │   ├── Models/                             # Recipient, NotificationBatch, Notification, NotificationOutbox
│   │   ├── OpenApi/                            # OpenAPI/Swagger описание
│   │   ├── Services/
│   │   │   ├── NotificationProviders/          # mock-провайдеры SMS/Email
│   │   │   └── ...                             # batch, outbox, publisher, reconciliation, history, provider events
│   │   └── Support/                            # вспомогательные утилиты (manual test delay)
│   ├── config/
│   │   └── notifications.php                   # rate limit, outbox, reconciliation, manual test delay
│   ├── database/
│   │   ├── factories/                          # factories для тестов
│   │   ├── migrations/                         # recipients, batches, notifications, outbox, jobs
│   │   └── seeders/                            # RecipientSeeder
│   ├── routes/                                 # api.php, web.php, console.php (scheduler)
│   ├── storage/api-docs/                       # сгенерированный api-docs.json для Swagger
│   └── tests/Feature/                          # feature/integration tests
├── README.md
├── TECHNICAL_NOTES.md
└── docker-compose.yml
</pre>

<br>
<h2>🚀 Как запустить проект</h2>
<ol>
  <li>
    <strong>Клонировать репозиторий:</strong>
    <pre><code>git clone https://github.com/vad-dom/notification-service.git</code></pre>
  </li>
  <li>
    <strong>Перейти в папку проекта:</strong>
    <pre><code>cd notification-service</code></pre>
  </li>
  <li>
    <strong>Собрать и запустить контейнеры:</strong>
    <pre><code>docker compose up -d --build</code></pre>
    <p>При этом автоматически:</p>
    <ul>
      <li>поднимутся PostgreSQL, Redis, RabbitMQ, PHP-FPM, Nginx и worker-контейнеры;</li>
      <li>установятся PHP-зависимости;</li>
      <li>создастся <code>.env</code> из <code>.env.example</code>, если его еще нет;</li>
      <li>выполнятся миграции;</li>
      <li>создадутся тестовые получатели через <code>RecipientSeeder</code>;</li>
      <li>запустятся worker'ы для очередей <code>notifications.outbox</code>, <code>notifications.critical</code> и <code>notifications.default</code>, а также scheduler для recovery-команд (<code>notifications:publish-outbox</code>, <code>notifications:reconcile-stuck</code>).</li>
    </ul>
  </li>
  <br>
  <li>
    <strong>Открыть приложение:</strong>
    <pre><code>http://localhost:8080</code></pre>
    <p>Главная страница автоматически перенаправляет на Swagger UI.</p>
  </li>
</ol>

<br>
<h2>📘 Swagger / OpenAPI</h2>

<p>Документация API доступна по адресу:</p>

<pre><code>http://localhost:8080/api/documentation</code></pre>

<p>Для проверки endpoint'ов в Swagger нажмите <strong>Authorize</strong> и укажите токены:</p>

<ul>
  <li>ApiToken: <code>test-token</code></li>
  <li>ProviderToken: <code>super-secret-token</code></li>
</ul>

<br>
<h2>📬 RabbitMQ UI</h2>

<p>RabbitMQ Management UI доступен по адресу:</p>

<pre><code>http://localhost:15672</code></pre>

<ul>
  <li>Логин: <code>notification_user</code></li>
  <li>Пароль: <code>notification_password</code></li>
</ul>

<br>
<h2>📋 Тестовые получатели</h2>

<p>После запуска проекта автоматически создаются 10 получателей.</p>

<p>Для тестирования API можно использовать:</p>

<pre><code>{
  "recipient_ids": [1, 2]
}</code></pre>

<br>
<h2>🔐 Авторизация API</h2>

<h3>Клиентское API</h3>
<p>Для создания рассылки и просмотра истории используется Bearer token:</p>

<pre><code>Authorization: Bearer test-token</code></pre>

<h3>Provider event endpoint</h3>
<p>Для подтверждения доставки сообщения от провайдера используется отдельный заголовок:</p>

<pre><code>X-Provider-Token: super-secret-token</code></pre>

<br>
<h2>✅ Как запустить тесты</h2>

<p>Внутри контейнера PHP:</p>

<pre><code>docker compose exec php php artisan test</code></pre>

<p>Или для запуска конкретного теста:</p>

<pre><code>docker compose exec php php artisan test --filter NotificationBatchApiTest</code></pre>

<p>End-to-end сценарий (API → очередь → провайдер → webhook):</p>

<pre><code>docker compose exec php php artisan test --filter NotificationDeliveryIntegrationTest</code></pre>

<h3>⚠️ Важно</h3>
<p>Автоматические тесты используют SQLite in-memory базу, заданную в <code>phpunit.xml</code>. Основная PostgreSQL база при запуске тестов не очищается.</p>

<br>
<h2>⚙️ Переменные окружения <code>src/.env</code></h2>

<h3>Лимиты API</h3>
<ul>
  <li><code>NOTIFICATION_BATCH_RATE_LIMIT_PER_MINUTE</code> — сколько batch-запросов разрешено с одного API-токена в минуту (защита от перегрузки).</li>
  <li><code>NOTIFICATION_BATCH_RATE_LIMIT_DECAY_SECONDS</code> — через сколько секунд «окно» этого лимита сбрасывается.</li>
</ul>

<h3>Outbox (публикация в очередь)</h3>
<ul>
  <li><code>NOTIFICATION_OUTBOX_RELAY_QUEUE</code> — имя очереди RabbitMQ, в которую попадает job для relay outbox (по умолчанию <code>notifications.outbox</code>).</li>
  <li><code>NOTIFICATION_OUTBOX_PUBLISH_LIMIT</code> — сколько неопубликованных outbox-записей обрабатывает одна команда <code>notifications:publish-outbox</code> за запуск.</li>
</ul>

<h3>Recovery для зависших <code>queued</code></h3>
<ul>
  <li><code>NOTIFICATION_STUCK_QUEUED_THRESHOLD_MINUTES</code> — через сколько минут в статусе <code>queued</code> уведомление считается «зависшим» и может быть переотправлено командой <code>notifications:reconcile-stuck</code>.</li>
  <li><code>NOTIFICATION_RECONCILIATION_PUBLISH_LIMIT</code> — сколько таких уведомлений обрабатывается за один запуск reconcile.</li>
</ul>

<h3>Ручная проверка: <code>NOTIFICATION_MANUAL_TEST_DELAY_SECONDS</code></h3>

<p>Пауза в секундах перед ключевыми шагами worker'ов. По умолчанию <code>0</code> — выключено.</p>

<p>Зачем: при ручной проверке весь путь уведомления (API → outbox → очередь → отправка провайдеру) проходит слишком быстро — в RabbitMQ UI и PostgreSQL сложно увидеть промежуточные статусы (<code>pending</code>, <code>queued</code>), сообщения в разных очередях и работу recovery. Значение <code>15–30</code> замедляет worker'ы и дает время наблюдать.</p>

<p>Задержка включается в <code>PublishNotificationOutboxJob</code> (relay outbox) и <code>SendNotificationJob</code> (отправка провайдеру). Для production и автотестов оставляйте <code>0</code>. После изменения — <code>php artisan config:clear</code>.</p>

<br>
<h2>🔄 Полная пересборка проекта</h2>

<p>Если нужно полностью пересоздать контейнеры, volumes и базу:</p>

<pre><code>docker compose down -v
docker compose up -d --build</code></pre>
