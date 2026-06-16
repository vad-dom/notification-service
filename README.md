<h1>Notification Service</h1>
<p>Микросервис уведомлений: массовая рассылка SMS/Email.</p>

<p>Используемые технологии и технические решения: <a href="TECHNICAL_NOTES.md">TECHNICAL_NOTES.md</a></p>

<br>
<h2>📂 Структура проекта</h2>
<pre>
├── docker/                                      # настройки Docker
├── src/
│   ├── app/
│   │   ├── DTO/                                # DTO для входных данных и результата создания batch
│   │   ├── Enums/                              # статусы, каналы, типы и приоритеты уведомлений
│   │   ├── Exceptions/                         # доменные исключения
│   │   ├── Http/
│   │   │   ├── Controllers/Api/                # API-контроллеры
│   │   │   ├── Middleware/                     # API token, provider token, Idempotency-Key
│   │   │   ├── Requests/                       # валидация API-запросов
│   │   │   └── Resources/                      # форматирование API-ответов
│   │   ├── Interfaces/                         # интерфейс провайдера уведомлений
│   │   ├── Jobs/                               # SendNotificationJob
│   │   ├── Models/                             # Recipient, NotificationBatch, Notification
│   │   ├── OpenApi/                            # OpenAPI/Swagger описание
│   │   └── Services/                           # бизнес-логика, publisher, resolver, mock-провайдеры
│   ├── database/
│   │   ├── factories/                          # factories для тестов
│   │   ├── migrations/                         # таблицы recipients, batches, notifications
│   │   └── seeders/                            # RecipientSeeder
│   ├── routes/                                 # api.php, web.php
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
      <li>создастся <code>.env</code> из <code>.env.example</code>, если его ещё нет;</li>
      <li>выполнятся миграции;</li>
      <li>создадутся тестовые получатели через <code>RecipientSeeder</code>;</li>
      <li>запустятся worker'ы для очередей <code>notifications.critical</code> и <code>notifications.default</code>.</li>
    </ul>
  </li>
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
  <li><code>ApiToken</code>: <code>test-token</code></li>
  <li><code>ProviderToken</code>: <code>super-secret-token</code></li>
</ul>

<br>
<h2>📬 RabbitMQ UI</h2>

<p>RabbitMQ Management UI доступен по адресу:</p>

<pre><code>http://localhost:15672</code></pre>

<p>Логин и пароль берутся из <code>docker-compose.yml</code>.</p>

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
<p>Для callback/event от провайдера используется отдельный заголовок:</p>

<pre><code>X-Provider-Token: super-secret-token</code></pre>

<br>
<h2>✅ Как запустить тесты</h2>

<p>Внутри контейнера PHP:</p>

<pre><code>docker compose exec php php artisan test</code></pre>

<p>Или для запуска конкретного теста:</p>

<pre><code>docker compose exec php php artisan test --filter NotificationBatchApiTest</code></pre>

<h3>⚠️ Важно</h3>
<p>Автоматические тесты используют SQLite in-memory базу, заданную в <code>phpunit.xml</code>. Основная PostgreSQL база при запуске тестов не очищается.</p>

<br>
<h2>🔄 Полная пересборка проекта</h2>

<p>Если нужно полностью пересоздать контейнеры, volumes и базу:</p>

<pre><code>docker compose down -v
docker compose up -d --build</code></pre>
