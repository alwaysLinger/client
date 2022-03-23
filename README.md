### INSTALLATION
```shell
composer require yylh/client
```
### 查看del是否正确 send方法加判断
### USAGE
```PHP
# client
$c = new Client('stream://127.0.0.1:9527');

$c->onConnect(function (Client $client) {
    dump($client);
});
$c->onReceive(function (Client $client, string $payload) {
    dump($payload);
    $client->send('client echo');
});

$c->start();

# server
$server = new Swoole\Server('127.0.0.1', 9527);

$server->set([
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0,
    'package_body_offset' => 4,
]);

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
});

$server->on('close', function ($server, $fd) {
    echo "connection close: {$fd}\n";
});

$server->start();

```