# olx-watcher
 Tracking OLX product prices and price changes

Requirements:
PHP ^8.2, composer, git
composer/autoload psr-4

```
Init cmd:
git clone 
cd /project/olx-watcher
cp ./config.ini.examlpe ./config.ini
composer install
```

Product price checking service: OlxWatcher\WatcherService

Subscription Service: OlxWatcher\SubscribeService

Parser script of products price: OlxWatcher\Parser

Saving subscribers and  prices of products is implemented in json files: OlxWatcher\CacheFileService

In the future it will be possible to use file cache or Redis: OlxWatcher\CacheRedisService

Sample subscription file: subscribe_example.json

The mailing uses sendmail: OlxWatcher\Mail\Mailer

The logging service has not yet been implemented.

To subscribe, you need to make a GET request with two parameters:
your email address and URL of the source OLX - advertisements for the sale of product.

Example:
```
http://example-olx-watcher/index.php?status=subscribe&email=test@mail.com&url=https://www.olx.ua/powerbank.html
```

To unsubscribe from all subscriptions you must send a GET request:
```
http://example-olx-watcher/index.php?email=test@mail.com&status=unsubscribe
```

The cron script checks every 15 minutes for changes in the price of a product and, if there is a change, sends emails to subscribers.
   
