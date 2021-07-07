<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class ProductLinksParser extends BaseParser
{
    public function parse(string $url): array
    {
        $partsUrl = parse_url($url);
        $baseUrl = "{$partsUrl['scheme']}://{$partsUrl['host']}/";

        $pages = $this->parsePagination($url);
        $numPages = $pages ?: 1;

        $result = [];

        for ($i = 1; $i <= $numPages; $i++) {
            $response = (string) $this->httpClient->request('GET', "$url?page=$i")->getBody();
            $crawler = new Crawler($response);
            $products = $crawler->filter('.one-block-catalog');

            $products->each(function (Crawler $node, $i) use ($baseUrl, &$result) {
                $result[] = $baseUrl . $node->filter('.one-block-catalog__name')->attr('href');
            });
        }

        return $result;
    }

    public function parsePagination(string $url): int
    {
        $response = (string) $this->httpClient->request('GET', $url)->getBody();
        $crawler = new Crawler($response);

        $pagination = $crawler->filter('.pagin_bottom a');

        return $pagination->count();
    }
}