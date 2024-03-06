# olx-watcher
 Tracking OLX product prices and price changes

Requirements:
PHP v8.2^, composer, git

```
Init cmd:
composer install
chmod 777 ./src/subscribe.json
chmod 777 ./src/updated_keys.json
cp ./.docker/runtimes/app_crontab /etc/cron.d/app_crontab
```

To subscribe, you need to make a GET request with two parameters:
your email address and URL of the source OLX - advertisements for the sale of product.
Example:
```
http://example-olx-watcher/index.php?email=test@mail.com&url=https://www.olx.ua/powerbank.html
```

The cron script checks every 15 minutes for changes in the price of a product and, if there is a change, sends emails to subscribers.

```
At the moment, not yet implemented:
caching of subscribe in Redis 
and an event logging service.
Caching is done in files.
```