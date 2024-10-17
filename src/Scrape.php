<?php

namespace App;

require_once 'vendor/autoload.php';

class Scrape
{

    private array $products = [];

    /**
     * Main method that runs the scraping process.
     */
    public function run(): void
    {
        $site = ScrapeHelper::fetchDocument('https://www.magpiehq.com/developer-challenge/smartphones');
        $pageLinks = $site->filter('#pages a');

        // Call the scrapeProducts method to extract products from the pages and store them in $this->products
        $this->products = Product::scrapeProducts($pageLinks);

        // Save the scraped products as a JSON file with pretty formatting and unescaped slashes
        file_put_contents('output.json', json_encode($this->products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

$scrape = new Scrape();
$scrape->run(); // Call the run() method to execute the scraping process
