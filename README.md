# mercadopago-sdk-php

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

* [Install](#install)
* [Basic checkout](#basic-checkout)
* [Customized checkout](#custom-checkout)

<a name="install"></a>
## Install

Via Composer

``` bash
$ composer require fedeisas/mercadopago-sdk-php
```

Or as a dependency in your project's composer.json:

```json
{
    "require": {
        "fedeisas/mercadopago-sdk-php": "1.0"
    }
}
```

<a name="basic-checkout"></a>
## Basic checkout

### Configure your credentials

* Get your **CLIENT_ID** and **CLIENT_SECRET** in the following address:
    * Argentina: [https://www.mercadopago.com/mla/herramientas/aplicaciones](https://www.mercadopago.com/mla/herramientas/aplicaciones)
    * Brazil: [https://www.mercadopago.com/mlb/ferramentas/aplicacoes](https://www.mercadopago.com/mlb/ferramentas/aplicacoes)
    * Mexico: [https://www.mercadopago.com/mlm/herramientas/aplicaciones](https://www.mercadopago.com/mlm/herramientas/aplicaciones)
    * Venezuela: [https://www.mercadopago.com/mlv/herramientas/aplicaciones](https://www.mercadopago.com/mlv/herramientas/aplicaciones)
    * Colombia: [https://www.mercadopago.com/mco/herramientas/aplicaciones](https://www.mercadopago.com/mco/herramientas/aplicaciones)
    * Chile: [https://www.mercadopago.com/mlc/herramientas/aplicaciones](https://www.mercadopago.com/mlc/herramientas/aplicaciones)

```php
use MercadoPago\MercadoPago;
use MercadoPago\Http\GuzzleClient;

$mp = new MercadoPago(new GuzzleClient());
$mp->setCredentials('CLIENT_ID', 'CLIENT_SECRET');
```

### Preferences

#### Get an existent Checkout preference

```php
$preference = $mp->getPreference('PREFERENCE_ID');

var_dump($preference);
```

#### Create a Checkout preference

```php
$preference_data = [
    'items' => [
        [
            'title' => 'Test',
            'quantity' => 1,
            'currency_id' => 'USD',
            'unit_price' => 10.4,
        ]
    ]
];

$preference = $mp->createPreference($preference_data);

var_dump($preference);
```

#### Update an existent Checkout preference

```php
$preference_data = [
    'items' => [
        [
            'title' => 'Test Modified',
            'quantity' => 1,
            'currency_id' => 'USD',
            'unit_price' => 20.4,
        ]
    ]
];

$preference = $mp->updatePreference('PREFERENCE_ID', $preference_data);

var_dump($preference);
```

### Payments/Collections

#### Search for payments

```php
$filters = [
    'id' => null,
    'site_id' => null,
    'external_reference' => null,
];

$searchResult = $mp->searchPayments($filters);

var_dump($searchResult);
```

#### Get payment data

```php
use MercadoPago\MercadoPago;
use MercadoPago\Http\GuzzleClient;

$mp = new MercadoPago(new GuzzleClient());
$mp->setCredentials('CLIENT_ID', 'CLIENT_SECRET');
$paymentInfo = $mp->getPayment('PAYMENT_ID');

var_dump($paymentInfo);
```

#### Cancel (only for pending payments)

```php
$result = $mp->cancelPayment('PAYMENT_ID');

var_dump($result);
```

#### Refund (only for accredited payments)

```php
$result = $mp->refundPayment('PAYMENT_ID');

var_dump($result);
```

<a name="custom-checkout"></a>

## Customized checkout

Use an access token:

```php
use MercadoPago\MercadoPago;
use MercadoPago\Http\GuzzleClient;

$mp = new MercadoPago(new GuzzleClient());
$mp->setAccessToken('SOME_ACCESS_TOKEN');
```

### Create payment

```php
$mp->getClient()->post(
    '/v1/payments',
    $paymentData,
    ['access_token' => 'SOME_ACCESS_TOKEN']
);
```

### Create customer

```php
$mp->getClient()->post(
    '/v1/customers',
    ['email' => 'email@test.com'],
    ['access_token' => 'SOME_ACCESS_TOKEN']
);
```

### Get customer

```php
$mp->getClient()->get(
    '/v1/customers/CUSTOMER_ID',
    [],
    ['access_token' => 'SOME_ACCESS_TOKEN']
);
```

* View more Custom checkout related APIs in Developers Site
    * Argentina: [https://www.mercadopago.com.ar/developers](https://www.mercadopago.com.ar/developers)
    * Brazil: [https://www.mercadopago.com.br/developers](https://www.mercadopago.com.br/developers)
    * Mexico: [https://www.mercadopago.com.mx/developers](https://www.mercadopago.com.mx/developers)
    * Venezuela: [https://www.mercadopago.com.ve/developers](https://www.mercadopago.com.ve/developers)
    * Colombia: [https://www.mercadopago.com.co/developers](https://www.mercadopago.com.co/developers)

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email federicoisas@gmail.com instead of using the issue tracker.

## Credits

- [Fede Isas][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/fedeisas/mercadopago-sdk-php.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/fedeisas/mercadopago-sdk-php/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/fedeisas/mercadopago-sdk-php.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/fedeisas/mercadopago-sdk-php.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/fedeisas/mercadopago-sdk-php.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/fedeisas/mercadopago-sdk-php
[link-travis]: https://travis-ci.org/fedeisas/mercadopago-sdk-php
[link-scrutinizer]: https://scrutinizer-ci.com/g/fedeisas/mercadopago-sdk-php/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/fedeisas/mercadopago-sdk-php
[link-downloads]: https://packagist.org/packages/fedeisas/mercadopago-sdk-php
[link-author]: https://github.com/fedeisas
[link-contributors]: ../../contributors
