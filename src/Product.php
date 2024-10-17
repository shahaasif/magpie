<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

class Product
{
    public string $title;
    public float $price;
    public string $imageUrl;
    public int $capacityMB;
    public string $colour;
    public string $availabilityText;
    public bool $isAvailable;
    public string $shippingText;
    public ?string $shippingDate;

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

    public static function scrapeProducts(Crawler $pageLinks): array
    {
        $productData = [];

        $pageLinks->each(function (Crawler $page) use (&$productData) {
            $document = ScrapeHelper::fetchDocument(preg_replace('/^\.\.\//', 'https://www.magpiehq.com/developer-challenge/', $page->attr('href')));
            $products = $document->filter('.product');

            $products->each(function (Crawler $node) use (&$productData) {
                $title = $node->filter('.product-name')->text() . " " . $node->filter('.product-capacity')->text();

                $relativeImageUrl = $node->filter('img')->attr('src');
                $imageUrl = preg_replace('/^\.\.\//', 'https://www.magpiehq.com/developer-challenge/', $relativeImageUrl);

                $capacityMB = intval(preg_replace('/[^0-9]/', '', $node->filter('.product-capacity')->text())) * 1000; //1024 If we need actual Mb

                $colours = self::extractColours($node);
                $price = self::extractPrice($node);
                $availabilityText = self::extractAvailabliltyText($node);

                $isAvailable = strpos($availabilityText, 'Out of Stock') === false;

                $shippingText = "";
                $shippingDate = "";
                if ($node->filter('.my-4.text-sm.block.text-center')->count() > 1) {
                    $shippingText = $node->filter('.my-4.text-sm.block.text-center')->eq(1)->text();

                    $shippingDate = self::extractShippingDate($shippingText);
                }

                foreach ($colours as $colour) {
                    $productKey = $title . '-' . $capacityMB . '-' . $colour;

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

        return array_values($productData);
    }

    public static function extractColours(Crawler $node): array
    {
        $colors = [];
        $spanElements = $node->filter('span[data-colour]');
        $spanElements->each(function (Crawler $node) use (&$colors) {
            $colors[] = $node->attr('data-colour');
        });
        return $colors;
    }
    public static function extractPrice(Crawler $node)
    {
        $priceText = $node->filter('.my-8.block.text-center.text-lg')->text();
        $price = floatval(preg_replace('/[^\d.]/', '', $priceText));
        return $price;
    }
    public static function extractAvailabliltyText(Crawler $node)
    {
        $availabilityText = $node->filter('.my-4.text-sm.block.text-center')->text();
        $availabilityText = str_replace('Availability: ', '', $availabilityText);
        return $availabilityText;
    }
    public static function extractShippingDate($shippingText)
    {
        if (preg_match('/(\d{1,2})(?:st|nd|rd|th)?\s(\w+)\s(\d{4})|(\d{4})-(\d{2})-(\d{2})|\btomorrow\b/i', $shippingText, $matches)) {
            if (preg_match('/\btomorrow\b/i', $matches[0])) {
                $shippingDate = date('Y-m-d', strtotime('tomorrow'));
            } elseif (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $matches[0])) {
                $shippingDate = $matches[0];
            } else {
                $shippingDate = date('Y-m-d', strtotime($matches[0]));
            }
        }
        return $shippingDate;
    }
}
