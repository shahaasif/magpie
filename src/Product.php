<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

class Product
{
    // Declare properties of the Product class
    public string $title;
    public float $price;
    public string $imageUrl;
    public int $capacityMB;
    public string $colour;
    public string $availabilityText;
    public bool $isAvailable;
    public string $shippingText;
    public ?string $shippingDate;

    // Constructor to initialize the product object
    public function __construct(
        string $title,
        float $price,
        string $imageUrl,
        int $capacityMB,
        string $colour,
        string $availabilityText,
        bool $isAvailable,
        string $shippingText,
        ?string $shippingDate
    ) {
        $this->title = $title;
        $this->price = $price;
        $this->imageUrl = $imageUrl;
        $this->capacityMB = $capacityMB;
        $this->colour = $colour;
        $this->availabilityText = $availabilityText;
        $this->isAvailable = $isAvailable;
        $this->shippingText = $shippingText;
        $this->shippingDate = $shippingDate;
    }

    // Static method to scrape products from a given list of page links
    public static function scrapeProducts(Crawler $pageLinks): array
    {
        $productData = [];
        $pageLinks->each(function (Crawler $page) use (&$productData) {
            $document = ScrapeHelper::fetchDocument(preg_replace('/^\.\.\//', 'https://www.magpiehq.com/developer-challenge/', $page->attr('href')));
            $products = $document->filter('.product');

            // Iterate through each product on the page
            $products->each(function (Crawler $node) use (&$productData) {
                // Extract product title as combination of product name and capacity
                $title = $node->filter('.product-name')->text() . " " . $node->filter('.product-capacity')->text();

                // Get product image URL
                $relativeImageUrl = $node->filter('img')->attr('src');
                $imageUrl = preg_replace('/^\.\.\//', 'https://www.magpiehq.com/developer-challenge/', $relativeImageUrl);

                // Extract product capacity and convert to MB (or 1024 if you want actual MB conversion)
                $capacityMB = intval(preg_replace('/[^0-9]/', '', $node->filter('.product-capacity')->text())) * 1000;

                // Extract the available colors
                $colours = self::extractColours($node);

                // Extract the product price
                $price = self::extractPrice($node);

                // Extract the availability text
                $availabilityText = self::extractAvailabliltyText($node);

                // Check if the product is available (not out of stock)
                $isAvailable = strpos($availabilityText, 'Out of Stock') === false;

                // Extract shipping details if available
                $shippingText = "";
                $shippingDate = "";
                if ($node->filter('.my-4.text-sm.block.text-center')->count() > 1) {
                    $shippingText = $node->filter('.my-4.text-sm.block.text-center')->eq(1)->text();
                    // Extract and format shipping date
                    $shippingDate = self::extractShippingDate($shippingText);
                }

                // Loop through each color variant and create unique product key for each variant
                foreach ($colours as $colour) {
                    $productKey = $title . '-' . $capacityMB . '-' . $colour;

                    // Avoid adding duplicate products to the array
                    if (!isset($productData[$productKey])) {
                        $productData[$productKey] = new self(
                            $title,
                            $price,
                            $imageUrl,
                            $capacityMB,
                            $colour,
                            $availabilityText,
                            $isAvailable,
                            $shippingText,
                            $shippingDate
                        );
                    }
                }
            });
        });

        // Return the scraped product data as an array
        return array_values($productData);
    }

    // Static method to extract available colors from the product node
    public static function extractColours(Crawler $node): array
    {
        $colors = [];
        $spanElements = $node->filter('span[data-colour]');
        $spanElements->each(function (Crawler $node) use (&$colors) {
            $colors[] = $node->attr('data-colour');
        });

        return $colors;
    }

    // Static method to extract product price from the product node
    public static function extractPrice(Crawler $node): float
    {
        $priceText = $node->filter('.my-8.block.text-center.text-lg')->text();
        $price = floatval(preg_replace('/[^\d.]/', '', $priceText));

        return $price;
    }

    // Static method to extract availability text
    public static function extractAvailabliltyText(Crawler $node): string
    {
        $availabilityText = $node->filter('.my-4.text-sm.block.text-center')->text();
        $availabilityText = str_replace('Availability: ', '', $availabilityText);

        return $availabilityText;
    }

    // Static method to extract and format shipping date
    public static function extractShippingDate(string $shippingText): ?string
    {
        $shippingDate = "";

        // Match shipping date formats (e.g., tomorrow, YYYY-MM-DD, or "18 Oct 2024")
        if (preg_match('/(\d{1,2})(?:st|nd|rd|th)?\s(\w+)\s(\d{4})|(\d{4})-(\d{2})-(\d{2})|\btomorrow\b/i', $shippingText, $matches)) {
            // If "tomorrow" is found, set the shipping date to tomorrow's date
            if (preg_match('/\btomorrow\b/i', $matches[0])) {
                $shippingDate = date('Y-m-d', strtotime('tomorrow'));
            }
            // If a "YYYY-MM-DD" date is found, return it as is
            elseif (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $matches[0])) {
                $shippingDate = $matches[0];
            }
            // Convert other date formats (e.g., "18 Oct 2024") to "Y-m-d"
            else {
                $shippingDate = date('Y-m-d', strtotime($matches[0]));
            }
        }

        return $shippingDate;
    }
}
