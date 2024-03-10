# olx-watcher
 Tracking OLX product prices and price changes

Requirements:
PHP v8.2^, composer, git

```
Init cmd:
git clone 
composer install
chmod 777 ./src/subscribe.json
chmod 777 ./src/updated_keys.json
cp ./.docker/runtimes/app_crontab /etc/cron.d/app_crontab
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
http://example-olx-watcher/index.php?email=test@mail.com&url=https://www.olx.ua/powerbank.html
```

To unsubscribe from all subscriptions you must send a GET request:
```
http://example-olx-watcher/index.php?email=test@mail.com&status=unsubscribe
```

The cron script checks every 15 minutes for changes in the price of a product and, if there is a change, sends emails to subscribers.
