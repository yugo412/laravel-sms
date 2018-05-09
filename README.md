# Laravel SMS

Laravel SMS is a package that has abilities to send and receive SMS via SMS gateway from various vendors such as SMSgateway.me, Zenziva.id, etc.

## Available Vendors
- [SMSGateway.me](http://smsgateway.me/)
- [Zenziva.id](http://www.zenziva.id)

## Installations

Install package via Composer by running the command:

```
composer require yugo/smsgateway -vvv
```

Publish command using the command:

```
php artisan vendor:publish
```

Select package from ```yugo/smsgateway``` to automatically copy a config file to your config application directory.

Open your ```.env``` file and add a new key named ```SMS_VENDOR``` (see available vendors).

```
SMS_VENDOR=smsgatewayme
```

### SMSGateway.me

To enable and using SMSgateway.me vendor, you must set new configurations based on SMSgateway.me setting. Add two config values like as below.

```
SMSGATEWAYME_DEVICE=
SMSGATEWAYME_TOKEN=
```

### Zenziva.id

Login to your Zenziva.id dashboard account to get ```userkey``` and ```passkey``` value. Add ```userkey``` and ```passkey``` to ```.env``` file using these configurations.

```
ZENZIVA_USERKEY=userkey
ZENZIVA_PASSKEY=passkey
```


## Usage

Sending a new message using Laravel SMS is easy. You just import real-time facade from the package and call the available methods inside it.


```php
use Facades\Yugo\SMSGateway\Interfaces\SMS;
```

Now, you can use class SMS inside your PHP file. Every vendor has ```send(array $destinations, string $message)``` method.

Send example:

```php
SMS::send(['62891111111'], 'Hello, how are you?');
```

## Additional Methods

Some vendors have additional methods. For example, you can check balance when using Zenziva and check device when using SMSGateway.me.

### SMSGateway.me

```php
// get registred device information
SMS::device(?string $deviceId);

// get detailed information from message
SMS::info(int $id);

// cancel queued message
SMS::cancel(array $id);
```

### Zenziva.id

```php
// get credit balance
SMS::credit();
```

# License

MIT
