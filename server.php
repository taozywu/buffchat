<?php

/*
  <#日期 = "2017-7-19">
  <#时间 = "00:46:16">
  <#人物 = "buff" >
  <#备注 = "
 * code : 
 *       -1 => 错误消息
 *        2 => 全局消息
 *        3 => 通知注册用户成功消息
 *        4 => 初次登录显示用户列表
 *        5 => 更新用户列表--添加
 *        6 => 更新用户列表--减少
 * 
 * ">
 */
require_once '/var/www/html/buffchat/class/WebS.php';
if (php_sapi_name() !== 'cli') {
    exit("使用cli模式");
}

//$serv = new Swoole\Websocket\Server("192.168.1.109", 9501);
$serv = new buff\WebS("192.168.1.109", 9501);
$redis = new \Redis();
$redis->connect("127.0.0.1", 6379);
if ($redis->ping() !== "+PONG") {
    die("redis 连接失败!");
}
$serv->set(array(
    'daemonize'  => 0,
    'worker_num' => 2 //worker process num
//    'log_file'      => '/home/buff/swoole.log'
));
//回调函数 新建一个websocket连接时 触发的事件
$serv->on('Open', function($server, $req) use($serv) {
    global $redis;
    $serv->opening($redis, $req);
});
//当收到用户的消息时 触发事件
$serv->on('Message', function($server, $frame)use($serv) {
    global $redis;
    $serv->messaging($redis, $frame);
});

//当websocket 断开连接时 触发事件
$serv->on('Close', function($server, $fd) use($serv) {
    global $redis;
    $serv->closing($redis, $fd);
});

$serv->start();
