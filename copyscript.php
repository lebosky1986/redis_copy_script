<?php

//ignore_user_abort(true);//
ini_set('memory_limit', '512M');
set_time_limit(0);//



$source_redis = new \Redis();
$source_redis->connect('localhost',6380);
$source_redis->select(2);

$keysList = $source_redis->keys('*');

//print_r($keysList);exit;

$target_redis = new \Redis();
$target_redis->connect('192.168.240.91',6379);
$target_redis->select(2);



foreach($keysList as $k){

    $type = $source_redis->type($k);

    switch($type){
        case \Redis::REDIS_STRING:
            $val = $source_redis->get($k);
            $target_redis->set($k,$val);
            break;
        case \Redis::REDIS_LIST:
            $list = $source_redis->lRange($k, 0, -1);
            foreach($list as $val){
                $target_redis->rPush($k, $val);
            }
            break;
        case \Redis::REDIS_HASH:
            $hash = $source_redis->hGetAll($k);
            $target_redis->hMSet($k, $hash);
            break;
        case \Redis::REDIS_ZSET:
            $zset = $source_redis->zRange($k, 0, -1, true);
            foreach($zset as $val=>$score){
                $target_redis->zAdd($k, $score, $val);
            }
            break;
    }
    $val = "";
    $list = "";
    $hash = "";
    $zset = "";

}
echo "finish@";
exit;
?>
