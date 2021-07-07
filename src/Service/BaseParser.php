<?php

namespace App\Service;

use GuzzleHttp\Client;

class BaseParser
{
    protected $httpClient;

    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;
    }
}