
#!/bin/bash

# Make sure composer can update settings file
chmod 755 /home/site/wwwroot/html/sites/default/settings.php

# setup nginx
cp /home/site/wwwroot/nginx-setup /etc/nginx/sites-available/default
cp /home/site/wwwroot/nginx.conf.setup /etc/nginx/nginx.conf

# setup php-fpm
cp /home/site/wwwroot/www.conf.setup /usr/local/etc/php-fpm.d/www.conf

# Restart nginx
service nginx restart

# Ensure that git and zip are setup for composer
apt-get install zip git --yes
git config --global --add safe.directory /home/site/wwwroot/html/modules/contrib/wxt_library
#apt-get update

# If composer doesn't work install it using the following
if [ ! -f /home/site/ext/composer.phar ]; then
  mkdir /home/site/ext
  cd /home/site/ext
  curl -sS https://getcomposer.org/installer | php
fi

# Get Drush and composer working
cp /home/site/ext/composer.phar /usr/local/bin/composer && echo 'export PATH="/home/site/wwwroot/vendor/bin:$PATH"' >> ~/.bashrc && sh ~/.bashrc



