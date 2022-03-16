<?php

include '../../vendor/autoload.php';

use Al\Client\Client;

$c = new Client('stream://127.0.0.1:9527');

$c->onConnect(function (Client $client) {
    dump($client);
});
$c->onReceive(function (Client $client, string $payload) {
    dump($payload);
    $client->send('client echo');
});

$c->start();