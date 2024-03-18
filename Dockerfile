FROM php:8.3.3-fpm
ARG WORKDIR=/var/www/html/public
ENV DOCUMENT_ROOT=${WORKDIR}
ENV LARAVEL_PROCS_NUMBER=1
ENV DOMAIN=_
ENV CLIENT_MAX_BODY_SIZE=15M
ARG GROUP_ID=1000
ARG USER_ID=1000
ENV USER_NAME=www-data
ARG GROUP_NAME=www-data
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libfreetype6-dev libjpeg62-turbo-dev libzip-dev \
    libpng-dev libonig-dev libxml2-dev libpq-dev openssh-server \
    zip unzip supervisor sqlite3 nano cron mutt sendmail

# Install nginx
RUN apt-get update && apt-get install -y nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions zip, mbstring, exif, bcmath, intl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install  zip mbstring exif pcntl bcmath -j$(nproc) gd intl

# Install Redis and enable it
RUN pecl install redis  && docker-php-ext-enable redis

# Install the PHP pdo_mysql extention
RUN docker-php-ext-install pdo_mysql

# Install the PHP pdo_pgsql extention
RUN docker-php-ext-install pdo_pgsql

# Install PHP Opcache extention
RUN docker-php-ext-install opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR $WORKDIR

RUN rm -Rf /var/www/* && \
mkdir -p /var/www/html

ADD .docker/runtimes/index.php $WORKDIR/index.php
ADD .docker/runtimes/php.ini $PHP_INI_DIR/conf.d/
ADD .docker/runtimes/opcache.ini $PHP_INI_DIR/conf.d/
ADD .docker/runtimes/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

COPY .docker/runtimes/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN ln -s /usr/local/bin/entrypoint.sh /


RUN rm -rf /etc/nginx/conf.d/default.conf
RUN rm -rf /etc/nginx/sites-enabled/default
RUN rm -rf /etc/nginx/sites-available/default

RUN rm -rf /etc/nginx/nginx.conf

COPY .docker/runtimes/nginx.conf /etc/nginx/nginx.conf
COPY .docker/runtimes/default.conf /etc/nginx/conf.d/
# Copy hello-cron file to the cron.d directory
COPY .docker/runtimes/app_crontab /etc/cron.d/app_crontab
# Give execution rights on the cron job
RUN chmod 0755 /etc/cron.d/app_crontab
# Create the log file
RUN touch /var/log/cron.log
# Run the command on container startup
#CMD cron

#RUN touch /var/log/exim4/mainlog

RUN usermod -u ${USER_ID} ${USER_NAME}
RUN groupmod -g ${USER_ID} ${GROUP_NAME}

RUN mkdir -p /var/log/supervisor
RUN mkdir -p /var/log/nginx
RUN mkdir -p /var/cache/nginx

RUN chown -R ${USER_NAME}:${GROUP_NAME} /var/www && \
  chown -R ${USER_NAME}:${GROUP_NAME} /var/log/ && \
  chown -R ${USER_NAME}:${GROUP_NAME} /etc/supervisor/conf.d/ && \
  chown -R ${USER_NAME}:${GROUP_NAME} $PHP_INI_DIR/conf.d/ && \
  touch /var/run/nginx.pid && \
  chown -R $USER_NAME:$USER_NAME /var/cache/nginx && \
  chown -R $USER_NAME:$USER_NAME /var/lib/nginx/ && \
  chown -R $USER_NAME:$USER_NAME /var/run/nginx.pid && \
  chown -R $USER_NAME:$USER_NAME /var/log/supervisor && \
  chown -R $USER_NAME:$USER_NAME /etc/nginx/nginx.conf && \
  chown -R $USER_NAME:$USER_NAME /etc/nginx/conf.d/ && \
  chown -R ${USER_NAME}:${GROUP_NAME} /tmp

EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]

