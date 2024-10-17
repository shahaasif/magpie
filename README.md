## Magpie PHP Developer Challenge

My task was to gather data about the products listed on https://www.magpiehq.com/developer-challenge/smartphones

and the final output of my script is expected be an array of objects similar to the example below:

```
{
    "title": "iPhone 11 Pro 64GB",
    "price": 123.45,
    "imageUrl": "https://example.com/image.png",
    "capacityMB": 64000,
    "colour": "red",
    "availabilityText": "In Stock",
    "isAvailable": true,
    "shippingText": "Delivered from 25th March",
    "shippingDate": "2021-03-25"
}

```

I used this repository as a starter template.

Also attched is the `output.json`.

### Requirements

- PHP 7.4+
- Composer

### Setup

```
composer install
cd magpie-developer-challenge
git clone https://github.com/shahaasif/magpie.git
```

To run the scrape you can use `php src/Scrape.php`
