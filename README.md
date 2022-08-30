[![build](https://github.com/Enjoyzz/dotenv/actions/workflows/build.yml/badge.svg)](https://github.com/Enjoyzz/dotenv/actions/workflows/build.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Enjoyzz/dotenv/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/dotenv/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Enjoyzz/dotenv/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/dotenv/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FEnjoyzz%2Fdotenv%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/Enjoyzz/dotenv/master)

# Парсер .env файлов.

Загружаются ***.env.dist***, ***.env*** и остальные файлы в зависимости от APP_ENV. Например, если APP_ENV=test, будет попытка
загрузить .env.test

**Приоритет конфигурационных файлов**

- сначала загружается dist файл, но у наго приоритет самый низкий.
- потом загружается основной файл (.env), все параметры которые в нём определены - перезапишут dist
- и последний файл - это файл окружения, если он существует. все параметры в нем будут иметь наивысший приоритет, и они
  перезапишут предыдущие значения. Этот файл не обязателен.
- переменные окружения установленные системно, например через export и т.п. имеют наивысший приоритет, так как они не
  перезаписываются

# Установка

```php 
composer require enjoys/dotenv
```

# Использование

```php
use Enjoys\Dotenv\Dotenv;
 
$dotenv = new Dotenv(__DIR__);

# or you can change the default configuration file names, the default file name is specified
# $dotenv = new Dotenv(__DIR__, '.env', '.env.dist');

$dotenv->loadEnv(); // config available in $_ENV

# use putenv()
# $dotenv->loadEnv(true);  // config available in $_ENV and getenv()
```

# Дополнительные возможности
### Type Casting (приведение типов)
Все значения в .env файле являются строками (string), но иногда было бы хорошо явно указать тип.
Это возможно.
```shell
VAR = *true  #bool(true)
VAR = *int 42  #int(42)
VAR = "*int 42"  #int(42)
#и т.д.
```
- ***true** - return bool(true)
- ***false** - return bool(false)
- ***null** - return NULL
- ***int** `*int 42` return int(42)
- ***int8**
- ***int16**
- ***float** `*float 3.14`  return float(3.14)
- ***string** - `*string *int` return string(4) "*int"

***Внимание***
_Type Casting будет работать только при использовании этой библиотеки, при парсинге файла другими библиотеками или системой значения скорее всего приведены к типам не будут._
