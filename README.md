# Laravel Notifluxion 🚀

A scalable, versioned, future-proof, and provider-agnostic Notification engine for Laravel 10/11+.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mohitbdeshmukh/notifluxion.svg?style=flat-square)](https://packagist.org/packages/mohitbdeshmukh/notifluxion)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x_|_11.x-FF2D20.svg?style=flat-square)](https://laravel.com)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

---

## 🎯 Features

- **Multi-Channel Dispatching:** Built-in decoupled drivers for SMTP, SendGrid, Twilio SMS, and WhatsApp Cloud APIs avoiding massive vendor SDK bloat.
- **Queue Agnostic Engine:** Natively ships with independent `Sync`, `Database`, and high-speed `Redis` sub-job queue boundaries.
- **Failover / Fallback Routing:** Intelligent exception catchers that automatically increment backoff timers or silently bounce dead payloads to backup drivers on the fly!
- **Zero API N+1 Loops:** Sending an array of 5,000+ targeted user endpoints triggers a raw sub-batching bulk SQL insert natively avoiding database crashes.
- **Multi-Tenant Logging:** Built entirely with organizational `tenant_id` scopes out of the box dynamically isolated.

<br>

## 🔌 Quick Install

Install securely via Composer natively:

```bash
composer require mohitbdeshmukh/notifluxion
```

Execute the built-in scaffolding daemon to boot the config file and migrate the advanced multi-tenant background tables across to your database:

```bash
php artisan notify:install
```

Configure your `.env` variables securely natively:
```dotenv
NOTIFY_QUEUE_ENABLED=true
NOTIFY_QUEUE_STRATEGY=database
NOTIFY_SENDGRID_KEY=SG.your_token
TWILIO_SID=AC...
```

## 🚀 Basic Usage

The architecture is totally decoupled from standard rigid boilerplate setups. 

Send an aggressive dynamic template array to 5,000 endpoints:
```php
use Notifluxion\LaravelNotify\Facades\Notify;
use Notifluxion\LaravelNotify\Notifications\GenericNotification;

$users = ['mohit@example.com', 'admin@example.com'];

// Send multiple triggers instantaneously! 
Notify::send($users, [
    'subject' => 'System Scales Flawlessly!',
    'view' => 'emails.marketing', 
    'viewData' => ['name' => 'Mohit']
]);
```

Trigger the intelligent Background daemon (auto-retries, delay backoffs, and fallback drivers enforced natively!)
```bash
php artisan notify:work
```

## 🧪 Testing
The architecture natively contains a decoupled testing sandbox to execute your live triggers dynamically! 
```bash
vendor/bin/testbench notify:test-live
```

---

## 🔒 License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
