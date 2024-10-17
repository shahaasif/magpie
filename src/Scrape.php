<?php

namespace App;

require_once 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    public function run(): void
    {
        $site = ScrapeHelper::fetchDocument('https://www.magpiehq.com/developer-challenge/smartphones');

        $pageLinks = $site->filter('#pages a');

        $this->products = Product::scrapeProducts($pageLinks);

        file_put_contents('output.json', json_encode($this->products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

$scrape = new Scrape();
$scrape->run();
