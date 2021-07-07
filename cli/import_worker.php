<?php

// Load Opencart config
require_once(dirname(__DIR__) . '/config.php');

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Service\ProductParser;
use App\Service\ProductImport;
use GuzzleHttp\Client;

$connection = new AMQPStreamConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
$channel = $connection->channel();

$channel->queue_declare('parse_product', false, true, false, false);

$callback = function ($msg) {
    $data = json_decode($msg->body, true);

    $parser = new ProductParser(
        new Client(
            [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36',
                    'Referer' => 'https://miramall.ru/',
                ],
            ]
        )
    );

    $product = $parser->parse($data['link']);
    (new ProductImport())->import($product);

    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('parse_product', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();