# Laravel SMS Gateway

![StyleCI](https://styleci.io/repos/132700891/shield)

Laravel SMS is a package that has abilities to send and receive SMS via SMS gateway from various vendors such as SMSgateway.me, Zenziva.id, etc.

## Table of Contents

  - [Requirements](https://github.com/arvernester/laravel-sms#requirements)
  - [Available Vendors](https://github.com/arvernester/laravel-sms#available-vendors)
  - [Installation Instructions](https://github.com/arvernester/laravel-sms#installations)
    - [SMSGateway.me](https://github.com/arvernester/laravel-sms#smsgatewayme)
    - [Zenziva.id](https://github.com/arvernester/laravel-sms#zenzivaid)
  - [Usage](https://github.com/arvernester/laravel-sms#usage)
  - [Additional Methods](https://github.com/arvernester/laravel-sms#additional-methods)
    - [SMSGateway.me](https://github.com/arvernester/laravel-sms#smsgatewayme-1)
    - [Zenziva.id](https://github.com/arvernester/laravel-sms#zenzivaid-1)
  - [License](https://github.com/arvernester/laravel-sms#license)
  
## Requirements
 - PHP 7.0 or above.
 - cURL extension for PHP.
 - Laravel version 5.4 or above.

## Available Vendors
- [SMSGateway.me](http://smsgateway.me/) (```smsgatewayme```)
- [Zenziva.id](http://www.zenziva.id) (```zenziva```)

## Installation Instructions

Install package via Composer by running the command:

```
composer require yugo/smsgateway -vvv
```

Publish package assets using the command below:

```
php artisan vendor:publish
```

Select package from ```yugo/smsgateway``` to automatically copy a config file to your config application directory.

[![vendor-publish.gif](https://s9.postimg.cc/6bmdismi7/vendor-publish.gif)](https://postimg.cc/image/ki24e0xd7/)

**Note:** If you are using Laravel version 5.4, you must setup provider manually by adding ```Yugo\SMSGateway\Providers\SmsServiceProvider::class``` to your ```config/app.php``` file.

```php
App\Providers\AppServiceProvider::class,
App\Providers\AuthServiceProvider::class,
// App\Providers\BroadcastServiceProvider::class,
App\Providers\EventServiceProvider::class,
App\Providers\RouteServiceProvider::class,
Yugo\SMSGateway\Providers\SmsServiceProvider::class,
```       

Then, publish package vendor using command below.

```
php artisan vendor:publish --provider="Yugo\SMSGateway\Providers\SmsServiceProvider"
```


### SMSGateway.me

To enable and using SMSgateway.me vendor, you must set new configurations based on SMSgateway.me setting. Add two config values like as below.

```
SMS_VENDOR="smsgatewayme"
SMSGATEWAYME_DEVICE=
SMSGATEWAYME_TOKEN=
```

### Zenziva.id

Login to your Zenziva.id dashboard account to get ```userkey``` and ```passkey``` value. Add ```userkey``` and ```passkey``` to ```.env``` file using these configurations.

```
SMS_VENDOR="zenziva"
ZENZIVA_USERKEY=userkey
ZENZIVA_PASSKEY=passkey
```


## Usage

Sending a new message using Laravel SMS is easy. You just import real-time facade from the package and call the available methods inside it.


```php
use Facades\Yugo\SMSGateway\Interfaces\SMS;
```

Now, you can use class SMS inside your PHP file. Every vendor has ```send(array $destinations, string $message)``` method.

Quick example:

```php
SMS::send(['62891111111'], 'Hello, how are you?');
```

## Additional Methods

Some vendors have additional methods. For example, you can check balance when using Zenziva and check device when using SMSGateway.me.

### SMSGateway.me

```php
// get registered device information
SMS::device(?int $id); // $id is nullable

// get detailed information from message
SMS::info(int $id);

// cancel queued message
SMS::cancel(array $id);
```

By default, SMSgateway.me package will using API configuration from ```.env``` file (such as device and token). But, you can set device ID and token programmatically via application. For example:

```php
SMS::setDevice(12345) // make sure it's integer value
  ->setToken('secret-token')
  ->send(['08111111111'], 'Message with custom device and token.');
```

### Zenziva.id

```php
// get credit balance
SMS::credit();
```

If you want to set ```userkey``` and/or ```passkey``` manually, you can using ```setUser(string $user)``` and ```setPassword(string $password)``` method. For example:

```php
SMS::setUser('you')
  ->setPassword('secret')
  ->send(['08111111111'], 'Message with custom user and password.');
```

# License

MIT.
