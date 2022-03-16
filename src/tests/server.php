<?php

$server = new Swoole\Server('127.0.0.1', 9527);

$server->set([
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0,
    'package_body_offset' => 4,
]);

$server->on('start', function ($server) {
    echo "TCP Server is started at tcp://127.0.0.1:9527\n";
});

$server->on('connect', function ($server, $fd) {
    echo "connection open: {$fd}\n";
    $data = 'server echo';
    $pp = pack('N', strlen($data)) . $data;
    $server->send($fd, $pp);
});

$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    var_dump('recv:' . substr($data, 4));
    $data = '123123123123';
    $pp = pack('N', strlen($data)) . $data;
    $server->send($fd, $pp);
    usleep(100);
});

$server->on('close', function ($server, $fd) {
    echo "connection close: {$fd}\n";
});

$server->start();
