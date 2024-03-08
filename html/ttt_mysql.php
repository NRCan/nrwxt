<?php

print '<pre>';

$databases = array (
  'database' => getenv('DB_NAME'),
  'username' => getenv('DB_USER'),
  'password' => getenv('DB_PASSWORD'), 
  'prefix' => getenv('DB_PREFIX'),
  'host' => getenv('DB_HOST'), 
  'port' => '3306',
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
);

if (!empty($databases['password'])) {
  $databases['password'] = '*** STRING OF LENGTH '.strlen($databases['password']).' ***';
}

print_r($databases);

print 'done';

print '</pre>';
