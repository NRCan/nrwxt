<?php 
echo '<pre>';

error_reporting(E_ALL);

/*
$settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml'
$settings['redis.connection']['interface'] = 'PhpRedis'; 
$settings['redis.connection']['scheme'] = 'tls';
$settings['redis.connection']['host'] = getenv('REDIS_HOST');
$settings['redis.connection']['port'] = getenv('REDIS_PORT');
//$settings['redis.connection']['base'] = NULL;
$settings['redis.connection']['password'] = getenv('REDIS_PASSWORD');
$settings['cache']['default'] = 'cache.backend.redis';  

*/

$settings = [
  'host' => getenv('REDIS_HOST'),
  'port' => getenv('REDIS_PORT'),
  'password' => getenv('REDIS_PASSWORD'),
];
if (!empty($settings['password'])) {
  $settings['password'] = '*** STRING OF LENGTH '.strlen($settings['password']).' ***';
}

print_r($settings);

$redis = new Redis([
    'host' => getenv('REDIS_HOST'),
    'port' => intval(getenv('REDIS_PORT')),
    'connectTimeout' => 3.5,
    'auth' => getenv('REDIS_PASSWORD'),
    //'ssl' => ['verify_peer' => false],
    'backoff' => [
        'algorithm' => Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
        'base' => 500,
        'cap' => 750,
    ],
]);

echo 'Now create/set the key: <b>key-name</b><br>';

$redis->set("key-name", "Esempio Redis"); //create a key with a value

echo 'This the value in the key: <b>'.$redis->get("key-name").'</b><br>';

echo 'all present keys:';
$allKeys = $redis->keys('*');
print_r($allKeys);

/*
echo "to clean up the DB I do a flush:<br>";
$redis->flushDB();

echo "or I can delete all the keys as follows:<br>";
$redis->delete($redis->keys('*'));

echo 'all keys present now:';
$allKeys = $redis->keys('*');
print_r($allKeys);
*/
echo '</pre>';

print 'done';
