<?php

// Load Opencart config
require_once(dirname(__DIR__) . '/config.php');

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\ProductLinksParser;
use GuzzleHttp\Client;

$parser = new ProductLinksParser(
    new Client(
        [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36',
                'Referer' => 'https://miramall.ru/',
            ],
        ]
    )
);

$connection = new AMQPStreamConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
$channel = $connection->channel();
$channel->queue_declare('parse_product', false, true, false, false);

$links = $parser->parse('https://miramall.ru/catalog/bazovie_stroitelnie_materiali/suhie_smesi_gartsovka/');

foreach ($links as $link) {
    $msg = new AMQPMessage(
        json_encode(['link' => $link]),
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );

    $channel->basic_publish($msg, '', 'parse_product');
}

$channel->close();
$connection->close();