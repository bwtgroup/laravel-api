<p align="right">
English description | <a href="README_RU.md">Russian description</a> 
</p>

# Laravel 5 API

[![Latest Stable Version][ico-stable-version]][link-stable-packagist]
[![Latest Unstable Version][ico-unstable-version]][link-unstable-packagist]
[![License][ico-license]](LICENSE.md)

This package allows to easily and quickly setup basics for API.

### Content

- [Installation](#installation)
- [Setup in Laravel](#setup-in-laravel)
- [Setup in Lumen](#setup-in-lumen)
- [License](#license)

### Installation

Install this package with composer using the following command:

```bash
composer require bwt-team/laravel-api
```

### Setup in Laravel

When composer updated, add service provider into `providers` array in `config/app.php`. 

```php
BwtTeam\LaravelAPI\Providers\ApiServiceProvider::class
```

This service provider will register `api` macros for more comfortable work. 
You will have the following format of call available in `api` format:
 
```php
response()->api($data)
```

In full format it will look as following: 

```php
new \BwtTeam\LaravelAPI\Response\ApiResponse($data)
```

Also, this service provider will allow to publish config file in order to update package settings according to your needs.
Use the following command for publication:


```bash
php artisan vendor:publish --provider="BwtTeam\LaravelAPI\Providers\ApiServiceProvider" --tag=config
```

To make all responses (including alerts etc) be sent in the same format, change class parent to `\BwtTeam\LaravelAPI\Exceptions\Handler` in  `App\Exceptions\Handler` class


```php
class Handler extends \BwtTeam\LaravelAPI\Exceptions\Handler
```

And middleware `\BwtTeam\LaravelAPI\Middleware\Api` should be connected to specific path (or the whole app) to make this path be handled as API method. 

If you are using `App\Http\Requests` class instances for validation, you need to inherit from `BwtTeam\LaravelAPI\Requests\ApiRequest`, rather than `Illuminate\Foundation\Http\FormRequest`.

### Setup in Lumen

After composer update register a service provider, by adding the following lines into `bootstrap/app.php`:

```php
$app->register(\BwtTeam\LaravelAPI\Providers\ApiServiceProvider::class);
```

Copy config file  `vendor/bwt-team/laravel-api/config/api.php` into config directory, which is stored in root directory (or create it yourself if it is missing) and set it up according to your needs.
To load settings from this file in `bootstrap/app.php`add the following lines:

```php
$app->configure('api');
```

To make all responses (including alerts etc) be sent in the same format, change class parent to BwtTeam\LaravelAPI\Exceptions\LumenHandler in App\Exceptions\Handler 

```php
class Handler extends \BwtTeam\LaravelAPI\Exceptions\LumenHandler
```

And middleware `\BwtTeam\LaravelAPI\Middleware\Api` should be connected to specific path (or the whole app) to make this path be handled as API method. 

### License

This package is using [MIT](LICENSE.md).

[ico-stable-version]: https://poser.pugx.org/bwt-team/laravel-api/v/stable?format=flat-square
[ico-unstable-version]: https://poser.pugx.org/bwt-team/laravel-api/v/unstable?format=flat-square
[ico-license]: https://poser.pugx.org/bwt-team/laravel-api/license?format=flat-square

[link-stable-packagist]: https://packagist.org/packages/bwt-team/laravel-api
[link-unstable-packagist]: https://packagist.org/packages/bwt-team/laravel-api#dev-develop