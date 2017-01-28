<p align="right">
<a href="README.md">Описание на английском</a> | Описание на русском 
</p>

# Laravel 5 API

[![Latest Stable Version][ico-stable-version]][link-stable-packagist]
[![Latest Unstable Version][ico-unstable-version]][link-unstable-packagist]
[![License][ico-license]](LICENSE.md)

Этот пакет помогает легко и быстро настроить каркас для создания API.
 
### Содержание

- [Установка](#Установка)
- [Настройка в Laravel](#Настройка-в-laravel)
- [Настройка в Lumen](#Настройка-в-lumen)
- [Лицензия](#Лицензия)

### Установка

Установите этот пакет с помощью composer используя следующую команду:

```bash
composer require bwt-team/laravel-api
```

### Настройка в Laravel

После обновления composer добавьте service provider в массив `providers` в файле `config/app.php`. 

```php
BwtTeam\LaravelAPI\Providers\ApiServiceProvider::class
```

Этот service provider зарегистрирует макрос `api` для более комфортной работы.
Вам станет доступным следующий формат вызова ответа в формате `api`:
 
```php
response()->api($data)
```

Аналогом этой записи в полном формате будет:

```php
new \BwtTeam\LaravelAPI\Response\ApiResponse($data)
```

Также этот service provider предоставит возможность опубликовать конфигурационный файл, чтоб изменить настройки пакета исходя из ваших потребностей.
Для публикации используйте команду:

```bash
php artisan vendor:publish --provider="BwtTeam\LaravelAPI\Providers\ApiServiceProvider" --tag=config
```

Для того чтоб все сообщения (включая сообщения об ошибках и т.п.) отдавались в едином формате, необходимо в классе `App\Exceptions\Handler` изменить родителя класса на
`\BwtTeam\LaravelAPI\Exceptions\Handler`

```php
class Handler extends \BwtTeam\LaravelAPI\Exceptions\Handler
```

А к конкретному пути (или ко всему приложению) необходимо подключить middleware `\BwtTeam\LaravelAPI\Middleware\Api`, чтоб данный путь обрабатывался как метод API.

Если для валидации вы используете экземпляры классов `App\Http\Requests`, то вам необходимо наследоваться не от `Illuminate\Foundation\Http\FormRequest`, а от `BwtTeam\LaravelAPI\Requests\ApiRequest`.

### Настройка в Lumen

После обновления composer зарегистрируйте service provider, добавив в файле `bootstrap/app.php` следующие строчки:

```php
$app->register(\BwtTeam\LaravelAPI\Providers\ApiServiceProvider::class);
```

Скопируйте файл конфигураций `vendor/bwt-team/laravel-api/config/api.php` в папку config, находящуюсь в корневом каталоге (создайте папку сами, если она отсутствует), и настройте его в соответствии с вашими нуждами.
Для загрузки настроек из этого файла в файле `bootstrap/app.php` добавьте следующие строчки:

```php
$app->configure('api');
```

Для того чтоб все сообщения (включая сообщения об ошибках и т.п.) отдавались в едином формате, необходимо в классе App\Exceptions\Handler изменить родителя класса на \BwtTeam\LaravelAPI\Exceptions\LumenHandler

```php
class Handler extends \BwtTeam\LaravelAPI\Exceptions\LumenHandler
```

А к конкретному пути (или ко всему приложению) необходимо подключить middleware `\BwtTeam\LaravelAPI\Middleware\Api`, чтоб данный путь обрабатывался как метод Api.

### Лицензия

Этот пакет использует лицензию [MIT](LICENSE.md).

[ico-stable-version]: https://poser.pugx.org/bwt-team/laravel-api/v/stable?format=flat-square
[ico-unstable-version]: https://poser.pugx.org/bwt-team/laravel-api/v/unstable?format=flat-square
[ico-license]: https://poser.pugx.org/bwt-team/laravel-api/license?format=flat-square

[link-stable-packagist]: https://packagist.org/packages/bwt-team/laravel-api
[link-unstable-packagist]: https://packagist.org/packages/bwt-team/laravel-api#dev-develop