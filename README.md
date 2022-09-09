[![build](https://github.com/Enjoyzz/dotenv/actions/workflows/build.yml/badge.svg)](https://github.com/Enjoyzz/dotenv/actions/workflows/build.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Enjoyzz/dotenv/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/dotenv/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Enjoyzz/dotenv/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/dotenv/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FEnjoyzz%2Fdotenv%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/Enjoyzz/dotenv/master)

# Парсер .env файлов.

Загружаются ***.env.dist***, ***.env*** и остальные файлы в зависимости от APP_ENV. Например, если APP_ENV=test, будет
попытка
загрузить .env.test

**Приоритет конфигурационных файлов**

- сначала загружается dist файл, но у него приоритет самый низкий.
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
 
# loaded line __DIR__.'/.env.dist -> __DIR__.'/.env' -> __DIR__.'/.env.<APP_ENV> (repeat.env.<APP_ENV> if redefined) 
$dotenv = new Dotenv(__DIR__.'/.env'); 
// config available in $_ENV
$dotenv->loadEnv(); 

# config available in $_ENV and getenv()
# $dotenv->loadEnv(true); 
```

# Формат .env файлов

```shell
VAR1 = value # можно использовать пробелы вокруг знака `=`
VAR2=value2
VAR3="this is value #3"
VAR4=value 4 #return `value 4
VAR5=${VAR4}2 # variable, return `value 42`
VAR6="it\'s a beautiful life"
VAR7 # if set Parser::AUTO_CAST_VALUE_TYPE return `null`, else empty string
VAR8= #return empty string
VAR9=${NOT_DEFINED_VAR:-value} # VAR9='value', but NOT_DEFINED_VAR - not set
VAR10=${NOT_DEFINED_VAR:=value} # VAR10='value' and  NOT_DEFINED_VAR='value'
VAR10=${NOT_DEFINED_VAR:?} # throw Exception
```

# Дополнительные возможности

### Type Casting (приведение типов)


Все значения в .env файле являются строками (string), но иногда было бы хорошо привести значение к соответствующему типу.
Это возможно, установив свойство $castType в true с помощью метода `enableCastType()`.

```php
use Enjoys\Dotenv\Dotenv;
$dotenv = new  Dotenv(__DIR__.'/.env');
$dotenv->enableCastType();
$dotenv->loadEnv();
```

...или с помощью флага `Dotenv::CAST_TYPE_ENV_VALUE`

```php
use \Enjoys\Dotenv\Dotenv;
$dotenv = new Dotenv(__DIR__.'/.env', flags: Dotenv::CAST_TYPE_ENV_VALUE);
$dotenv->loadEnv();
```

[Доступные флаги](#flags)

Ниже примеры как будут кастоваться переменные
```shell
VAR = *true  #bool(true)
VAR = true  #bool(true)
VAR = *false  #bool(false)
VAR = false  #bool(false)
VAR = *bool somethig  #bool(true)
VAR = *bool  #bool(false)
VAR = *int 42  #int(42)
VAR = "*int 42"  #string
VAR = 42  #int(42)
VAR = '42'  #string
VAR = 3.14  #float(3.14)
VAR = 3,14  #float(3.14)
VAR = *float 3,14  #float(3.14)
VAR = *double 3.14  #float(3.14)
VAR = "3.14"  #string
#и т.д.
```

- ***bool** - return bool(true) or bool(false)
- ***true** - return bool(true)
- ***false** - return bool(false)
- ***null** - return NULL
- ***int** `*int 42` return int(42)
- ***int8**
- ***int16**
- ***float** or ***double** `*float 3,14`  return float(3.14), запятые автоматически заменяются на точки
- ***string** - `*string *int` return string(4) "*int"

***Внимание***
_Type Casting будет работать только при использовании этой библиотеки, при парсинге файла другими библиотеками или
системой значения скорее всего приведены к типам не будут._

### Значения переменных по-умолчанию

Если переменная не установлена, можно определить значения по-умолчанию.
Есть два варианта как это сделать:

1. `${VAR:-default}` - при этом варианте, если переменная не будет установлена, вернется значение после знака `:-`,
   переменная при
   этом также останется не установленной, в противном случае будет возвращено значение переменной.
2. `${VAR:=default}` - при этом варианте, если переменная не будет установлена, вернется значение после знака `:=` и
   установить переменную с этим значением, в противном случае будет возвращено значение переменной.

_**Внимание!** Если переменная не установлена, и не переданы значения по-умолчанию, будет возвращена пустая строка._

Чтобы вызвать ошибку при таком сценарии, можно указать после наименования переменной `:?`, или `:?message`. И в случае
если
переменная не была установлена, будет выброшено исключение `\Enjoys\Dotenv\Exception\InvalidArgumentException`

Например:

```shell
VAR1=${NOT_DEFINED_VAR:?extended error message} #with error message
VAR2=${NOT_DEFINED_VAR:?} #or just with empty error message
```

### <span id="flags"></span>Доступные флаги

- **CLEAR_MEMORY_AFTER_LOAD_ENV** - очищает память псле установки всех значений в $_ENV, $_SERVER или putenv()
- **CAST_TYPE_ENV_VALUE** - приводит к типу на основе содержимого (string|bool|int|float|null)
- **POPULATE_PUTENV** - будут доступны установленные значения помимо $_ENV также из getenv()
- **POPULATE_SERVER** - будут доступны установленные значения помимо $_ENV также из $_SERVER

Флаги можно комбинировать через `|`, например `Dotenv::CAST_TYPE_ENV_VALUE|Dotenv::POPULATE_PUTENV`
