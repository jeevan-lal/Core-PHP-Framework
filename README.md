# Core PHP Framework

## Installation

```sh
composer require ctechhindi/core-php-framework
```

## Libraries

- [Slim 4](https://www.slimframework.com/docs/v4/start/installation.html)
- [PHP Renderer](https://github.com/slimphp/PHP-View)

```
composer require slim/slim:"4.*"
composer require slim/psr7
composer require slim/php-view
```

## AutoLoad

```json
"autoload": {
  "psr-4": {
    "CTH\\": "public/App",
    "CTH\\System\\": "public/System"
  }
}
```

```
composer update
```

## Documentation

### Pre-Define Variables

```php
// Public Folder Path
echo PPATH;
// System Folder Path
echo SPATH;
// App Folder Path
echo APATH;
```

### Global Functions

```php
redirect()
set_timezone()
session()
baseURL()
helper()
```

### Helpers

Helpers methods use `controller` and `view` files.

#### Request

#### Encryption

#### Form
