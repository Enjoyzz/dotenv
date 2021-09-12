[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FEnjoyzz%2Fdotenv%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/Enjoyzz/dotenv/master)

# dotenv

Парсер .env файлов.

```php 
$dotenv = new \Enjoys\Dotenv\Dotenv(__DIR__);
$dotenv->loadEnv() //
```

Загружаются .env.dist, .env и остальные файлы в зависимости от APP_ENV. Например, если APP_ENV=test, будет попытка
загрузить .env.test

**Приоритет конфигурационных файлов**

- сначала загружается dist файл, но у наго приоритет самый низкий.
- потом загружается основной файл (.env), все параметры которые в нём определены - перезапишут dist
- и последний файл - это файл окружения, если он существует. все параметры в нем будут иметь наивысший приоритет, и они
  перезапишут предыдущие значения. Этот файл не обязателен.
- переменные окружения установленные системно, например через export и т.п. имеют наивысший приоритет, так как они не
  перезаписываются