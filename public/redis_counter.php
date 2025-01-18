<?php
$redis = new Redis();
$redis->connect('redis', 6379);
$key = 'access_count';
$count = $redis->exists($key) ? intval($redis->get($key)) : 0;
$count++;
$redis->set($key, strval($count));
?>
今のカウントは<?= $count ?>です
