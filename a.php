<?php

include "vendor/autoload.php";

use Al\Client\Attributes\ClientMeta;
use Al\Client\Attributes\Meta;
use Al\Client\Wrappers\TcpWrapper;
use Al\Events\Epoll;
use Al\Client\Protocols\Stream;
use Al\Client\Client;

#[ClientMeta(TcpWrapper::class, Epoll::class, Stream::class, Client::class)]
class A
{

}

$ref = new ReflectionClass(A::class);
$a = $ref->getAttributes(ClientMeta::class);
$b = $ref->getAttributes(Meta::class);
$meta = $a[0];
var_dump($meta->getArguments());

// var_dump($b);


