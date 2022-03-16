<?php

include 'vendor/autoload.php';

use Al\Client\Protocols\Stream;
use Al\Client\Wrappers\Wrapper;
use Al\Client\Attributes\WrapperMeta;

// #[WrapperMeta('N', 4, Stream::class)]
class A
{

}

$ref = new ReflectionClass(A::class);
$a = $ref->getAttributes(name:WrapperMeta::class);
$b = $ref->getAttributes(name:\Al\Client\Attributes\Meta::class);
var_dump($b);
// var_dump($a);
// var_dump($a[0]->getName());
// var_dump($a[0]->getArguments());
// var_dump($a[0]->newInstance());

