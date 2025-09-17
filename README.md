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

Все значения в .env файле являются строками (string), но иногда было бы хорошо привести значение к соответствующему
типу.
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

***Важное примечание***

**НЕ РЕКОМЕНДУЕТСЯ использовать флаг `Dotenv::CAST_TYPE_ENV_VALUE` или `enableCastType()`.**

_Хотя автоматическое приведение типов может быть удобным, его использование напрямую через `$_ENV` может быть
неочевидным
для других разработчиков и усложняет отладку. Значения, приведённые к типам, будут доступны только в рамках текущего
PHP-процесса, где была использована эта библиотека._

***Критически важно понимать***_, что при использовании функции `getenv()` (которая часто используется для чтения
переменных
окружения) всегда возвращаются строковые значения, даже если тип был приведён на уровне парсера и сохранён
в `$_ENV`. Это может привести к неожиданным результатам и ошибкам, если код использует смешанный доступ к переменным
через
`$_ENV` и `getenv()`._

_Также эта функция замедляет парсинг переменных, что особенно заметно на больших объёмах данных._

_Для более предсказуемого и согласованного поведения **рекомендуется использовать функцию** **`env()`**. Она
предоставляет те же
возможности по приведению типов, но делает это явно в момент обращения к значению. Это делает код читаемым и
предотвращает ошибки, связанные с неявным преобразованием типов и различиями в способах хранения переменных (
в `$_ENV`, `getenv()` или `$_SERVER`)._

```php
// Несогласованное поведение (опасно!):
$debugFromEnv = $_ENV['DEBUG'];    // bool(true) - если включен CAST_TYPE_ENV_VALUE
$debugFromGetenv = getenv('DEBUG'); // string('true') - всегда строка!

// Предсказуемое поведение с функцией env() (рекомендуется):
$debug1 = env('DEBUG', false);      // Всегда bool(false), если переменной нет
$debug2 = env('DEBUG', '*true');    // Всегда bool(true), если переменной нет
$debug3 = env('DEBUG');             // Значение будет приведено к типу корректно
```

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

- **CLEAR_MEMORY_AFTER_LOAD_ENV** - очищает память после установки всех значений в $_ENV, $_SERVER или putenv()
- **CAST_TYPE_ENV_VALUE** - приводит к типу на основе содержимого (string|bool|int|float|null)
- **POPULATE_PUTENV** - будут доступны установленные значения помимо $_ENV также из getenv()
- **POPULATE_SERVER** - будут доступны установленные значения помимо $_ENV также из $_SERVER

Флаги можно комбинировать через `|`, например `Dotenv::CAST_TYPE_ENV_VALUE|Dotenv::POPULATE_PUTENV`

# Функция `env()`

Удобная функция для работы с переменными окружения с поддержкой преобразования типов, валидации и гибкой обработки
значений.

## Базовое использование

```php
// Получение значения переменной окружения (вернет localhost если переменная не существует)
$dbHost = env('DB_HOST', 'localhost');

// Без значения по умолчанию (вернет null если переменная не существует)
$dbPort = env('DB_PORT');
```

## Преобразование типов

### Автоматическое преобразование типов

Функция использует ValueTypeCasting::castType() для автоматического преобразования типов:

#### Специальные префиксы для явного указания типов

```php
// Булевы значения
env('DEBUG', '*true');        // → bool(true)
env('DEBUG', '*false');       // → bool(false)
env('FLAG', '*bool something'); // → bool(true)
env('FLAG', '*bool');         // → bool(false)

// Числовые значения
env('PORT', '*int 8080');     // → int(8080)
env('PRICE', '*float 99,99'); // → float(99.99)
env('RATE', '*double 3.14');  // → float(3.14)

// Строки и null
env('NAME', '*string John');  // → string("John")
env('OPTIONAL', '*null');     // → null
```

#### Автоматическое преобразование без префиксов

```php
// Булевы значения
env('DEBUG', 'true');    // → bool(true)
env('DEBUG', 'false');   // → bool(false)

// Числовые значения  
env('PORT', '8080');     // → int(8080)
env('PRICE', '99.99');   // → float(99.99)
env('PRICE', '99,99');   // → float(99.99) - запятые автоматически заменяются

// Строки и null
env('NAME', 'John Doe'); // → string("John Doe")
env('OPTIONAL', 'null');     // → null
```

### Преобразование значений

```php
// Простое преобразование типов
$port = env('DB_PORT', 3306, fn($v) => (int) $v);
$debug = env('APP_DEBUG', false, fn($v) => (bool) $v);

// Комплексное преобразование
$allowedHosts = env('ALLOWED_HOSTS', 'localhost,127.0.0.1', function($v) {
    return array_filter(array_map('trim', explode(',', $v)));
});

// Использование встроенных функций PHP
$env = env('APP_ENV', 'prod', 'strtoupper');
$name = env('APP_NAME', 'My App', 'trim');
```

### Валидация значений

```php
// Простая валидация
$port = env('PORT', 8080, validator: function($v) {
    return $v >= 1 && $v <= 65535;
});

// Комплексная валидация с преобразованием
$email = env('ADMIN_EMAIL', validator: function($v) {
    return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : null;
}, function($v) {
    return $v !== null;
});

// Валидация с кастомными сообщениями
$apiKey = env('API_KEY', null, validator: function($v) use ($key) {
    if (empty($v)) {
        throw new InvalidArgumentException("API_KEY is required");
    }
    return strlen($v) >= 32;
});
```

### Комбинирование преобразования и валидации

```php
// Преобразование + валидация
$retryCount = env(
    key: 'RETRY_COUNT', 
    default: 3, 
    transform: fn($v) => (int) $v, // преобразование
    validator: fn($v) => $v >= 0 && $v <= 10 // валидация
);

// Сложный пример
$config = env('APP_CONFIG', '{}', 
    fn($v) => json_decode($v, true) ?? [],
    fn($v) => is_array($v) && !empty($v)
);
```

### Получение сырых значений

```php
// Получить значение без преобразования и валидации
$rawValue = env('SOME_VAR', raw: true);

// Полезно для отладки
var_dump(env('COMPLEX_CONFIG', raw: true));
```

## Практические примеры

Конфигурация приложения:

```php
return [
    'debug' => env('APP_DEBUG', false, fn($v) => (bool) $v),
    'env' => env('APP_ENV', 'production', 'strtolower'),
    'name' => env('APP_NAME', 'My App', 'trim'),
    
    'database' => [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306, fn($v) => (int) $v, fn($v) => $v > 0),
        'name' => env('DB_NAME', 'app'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', '', validator: fn($v) => !empty($v)),
    ],
    
    'api' => [
        'timeout' => env('API_TIMEOUT', 30.0, fn($v) => (float) $v),
        'retries' => env('API_RETRIES', 3, fn($v) => (int) $v, fn($v) => $v >= 0),
    ]
];
```

Безопасные значения с валидацией:

```php
// Email с валидацией
$adminEmail = env('ADMIN_EMAIL', null, 
    fn($v) => filter_var(trim($v), FILTER_VALIDATE_EMAIL),
    fn($v) => $v !== false
);

// Порты с валидацией диапазона
$port = env('APP_PORT', 8080, 
    fn($v) => (int) $v,
    fn($v) => $v >= 1 && $v <= 65535
);

// Enum-значения
$logLevel = env('LOG_LEVEL', 'info', 
    fn($v) => strtolower($v),
    fn($v) => in_array($v, ['debug', 'info', 'warning', 'error', 'critical'])
);
```

## Особенности работы

**Приоритеты получения значений**

Функция проверяет переменные в следующем порядке:

1. getenv() - системные переменные окружения
2. $_ENV - массив переменных окружения PHP
3. Значение по умолчанию

```php
// Если установлено и в getenv() и в $_ENV - используется getenv()
putenv('TEST_VAR=system_value');
$_ENV['TEST_VAR'] = 'env_value';

env('TEST_VAR'); // → "system_value"
```

**Обработка ошибок валидации**

При неудачной валидации выбрасывается `InvalidArgumentException` с информативным сообщением:

```php
// Выбросит исключение с сообщением:
// "Environment variable "API_PORT" validation failed. Got: 99999"
env('API_PORT', 99999, null, fn($v) => $v <= 65535);
```

