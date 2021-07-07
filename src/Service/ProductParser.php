<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use App\Entity\Product;

class ProductParser extends BaseParser
{
    public function parse(string $url): Product
    {
        $response = (string) $this->httpClient->request('GET', $url)->getBody();
        $crawler = new Crawler($response);

        $name = $crawler->filter('h1')->text();
        $priceText = $crawler->filter('.yellow-price')->text();
        $price = (float) preg_replace('/[^\d]/ius','', $priceText);
        $imageUrl = $crawler->filter('.card__top-left a')->attr('rev');

        $partsUrl = parse_url($url);
        $image = str_replace('..', "{$partsUrl['scheme']}://{$partsUrl['host']}", $imageUrl);

        return new Product(
            $name,
            $price,
            $image
        );
    }
}