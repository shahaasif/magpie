<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\RequestException;

class ScrapeHelper
{
    public static function fetchDocument(string $url): ?Crawler
    {
        // FOr Online uncomment below line
        // $client = new Client();
        // FOr Online comment below line
        $client = new Client(['verify' => false]);

        try {
            $response = $client->get($url);
            return new Crawler($response->getBody()->getContents(), $url);
        } catch (RequestException $e) {
            echo 'Request failed: ' . $e->getMessage() . "\n";
            return null;
        }
    }
}
