<?php

namespace App\Support;

class Catalog
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'Electronics' => 'Electronics',
            'Home' => 'Home goods',
            'Fashion' => 'Fashion',
            'Kitchen' => 'Kitchen',
            'Outdoors' => 'Outdoors',
            'Grocery' => 'Grocery',
            'Beauty' => 'Beauty',
            'Stationery' => 'Stationery',
        ];
    }

    public static function label(string $category): string
    {
        return self::labels()[$category] ?? $category;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        static $products = null;

        if ($products === null) {
            $products = self::catalog();
        }

        return $products;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $id): ?array
    {
        foreach (self::all() as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function deals(): array
    {
        return array_slice(array_values(array_filter(
            self::all(),
            fn (array $product) => $product['compare'] && $product['stock'] !== 0,
        )), 0, 4);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function popular(): array
    {
        $products = self::all();
        usort($products, fn (array $a, array $b) => $b['reviews'] <=> $a['reviews']);

        return array_slice($products, 0, 8);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function more(): array
    {
        $popularIds = array_column(self::popular(), 'id');

        return array_slice(array_values(array_filter(
            self::all(),
            fn (array $product) => ! in_array($product['id'], $popularIds, true),
        )), 0, 4);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function related(array $product): array
    {
        return array_slice(array_values(array_filter(
            self::all(),
            fn (array $item) => $item['id'] !== $product['id'] && $item['category'] === $product['category'],
        )), 0, 4);
    }

    /**
     * @param  array{q?: string, cat?: string, max?: int, rated?: bool, stock?: bool, sort?: string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public static function search(array $filters): array
    {
        $query = mb_strtolower(trim($filters['q'] ?? ''));
        $category = $filters['cat'] ?? '';
        $max = (float) ($filters['max'] ?? 200);
        $rated = (bool) ($filters['rated'] ?? false);
        $inStock = (bool) ($filters['stock'] ?? false);

        $products = array_values(array_filter(self::all(), function (array $product) use ($query, $category, $max, $rated, $inStock) {
            if ($category !== '' && $product['category'] !== $category) {
                return false;
            }

            if ($product['price'] > $max) {
                return false;
            }

            if ($rated && $product['rating'] < 4) {
                return false;
            }

            if ($inStock && $product['stock'] === 0) {
                return false;
            }

            if ($query !== '') {
                $haystack = mb_strtolower($product['name'].' '.$product['category'].' '.$product['blurb']);
                if (! str_contains($haystack, $query)) {
                    return false;
                }
            }

            return true;
        }));

        $sort = $filters['sort'] ?? 'featured';

        if ($sort === 'price-asc') {
            usort($products, fn (array $a, array $b) => $a['price'] <=> $b['price']);
        } elseif ($sort === 'price-desc') {
            usort($products, fn (array $a, array $b) => $b['price'] <=> $a['price']);
        } elseif ($sort === 'rating') {
            usort($products, fn (array $a, array $b) => [$b['rating'], $b['reviews']] <=> [$a['rating'], $a['reviews']]);
        } elseif ($sort === 'reviews') {
            usort($products, fn (array $a, array $b) => $b['reviews'] <=> $a['reviews']);
        }

        return $products;
    }

    public static function money(int|float $amount): string
    {
        return '$'.number_format((float) $amount, 2);
    }

    public static function maxQty(array $product): int
    {
        if ($product['stock'] === 0) {
            return 0;
        }

        if (is_int($product['stock'])) {
            return $product['stock'];
        }

        return 12;
    }

    /**
     * @return array{text: string, class: string}
     */
    public static function availability(array $product): array
    {
        if ($product['stock'] === 0) {
            return ['text' => 'Sold out', 'class' => 'out'];
        }

        if (is_int($product['stock']) && $product['stock'] <= 5) {
            return ['text' => 'Only '.$product['stock'].' left', 'class' => 'low'];
        }

        return ['text' => $product['ship'], 'class' => ''];
    }

    public static function savePercent(array $product): ?int
    {
        if (! $product['compare']) {
            return null;
        }

        return (int) round((1 - $product['price'] / $product['compare']) * 100);
    }

    public static function stars(float $rating): string
    {
        $rounded = round($rating * 2) / 2;
        $html = '';

        for ($i = 1; $i <= 5; $i++) {
            if ($rounded >= $i) {
                $html .= '<i class="bi bi-star-fill"></i>';
            } elseif ($rounded >= $i - 0.5) {
                $html .= '<i class="bi bi-star-half"></i>';
            } else {
                $html .= '<i class="bi bi-star"></i>';
            }
        }

        return $html;
    }

    /**
     * @return array<int, array{title: string, body: string, stars: int, date: string}>
     */
    public static function reviews(): array
    {
        return [
            ['title' => 'Daily driver now', 'body' => 'Comfortable for long sessions and the finish matches the photos.', 'stars' => 5, 'date' => 'March 12, 2026'],
            ['title' => 'Solid for the price', 'body' => 'Arrived on the estimated day and worked right out of the box.', 'stars' => 4, 'date' => 'February 2, 2026'],
            ['title' => 'Would buy again', 'body' => 'Packed with care. The description matched what showed up.', 'stars' => 5, 'date' => 'January 18, 2026'],
        ];
    }

    private static function shot(string $id): string
    {
        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1000&q=80";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function catalog(): array
    {
        return [
            self::item('nb-headphones', 'Aero Quiet Headphones', 'Electronics', 129, 168, 4.7, 2143, 'Bestseller', null, 'Ships tomorrow', ['1505740420928-5e560c06d30e', '1484704849700-f032a568e944'], ['Charcoal', 'Sand', 'Forest'], [], 'Over-ear headphones with soft pads and a quiet-enough cabin for commutes and late work.', 'A fold-flat hinge, a 30-hour battery, and a cable in the case when you would rather plug in.', [['Driver', '40 mm'], ['Battery', '30 hours'], ['Weight', '252 g'], ['Connection', 'Bluetooth 5.3']]),
            self::item('nb-kettle', 'Harbor Pour-Over Kettle', 'Kitchen', 46, 59, 4.6, 890, 'Deal', null, 'Ships tomorrow', ['1517668808822-9ebb02f2a0e6', '1495474472287-4d71bcdd2085'], ['Matte black', 'Cloud'], [], 'A gooseneck kettle with a steady pour and a counter-friendly footprint.', 'Holds 900 ml, marks the fill line inside, and sits flat so it does not drip down the side.', [['Capacity', '900 ml'], ['Material', 'Stainless steel'], ['Heat', 'Stovetop'], ['Finish', 'Matte']]),
            self::item('nb-throw', 'Dune Linen Throw', 'Home', 64, null, 4.5, 412, 'New', null, 'Ships in 2 days', ['1522771739844-6a9f6d5f14af'], ['Oat', 'Moss', 'Ink'], [], 'A washed linen throw that softens after the first weekend on the sofa.', 'Loose weave, fringed edges, and a size that covers a two-seat sofa without pooling on the floor.', [['Size', '130 × 180 cm'], ['Fabric', 'Washed linen'], ['Care', 'Cold wash'], ['Weight', 'Light']]),
            self::item('nb-runners', 'Trailform Runners', 'Outdoors', 98, 128, 4.8, 1560, 'Bestseller', null, 'Ships tomorrow', ['1542291026-7eec264c27ff', '1460353581641-37baddab0fa2'], ['Clay', 'Night'], ['7', '8', '9', '10', '11'], 'Road-to-trail shoes with a grippy outsole and a collar that does not bite.', 'Daily mileage cushion, a reflective heel tab, and a lace garage so the bow stays put.', [['Drop', '8 mm'], ['Use', 'Road and light trail'], ['Upper', 'Recycled mesh'], ['Closure', 'Laces']]),
            self::item('nb-bottle', 'Ridge Steel Bottle', 'Outdoors', 28, null, 4.4, 2301, null, null, 'Ships tomorrow', ['1602143407151-7111542de6e8'], ['Slate', 'Citrus', 'Sea'], [], 'A 750 ml bottle that keeps water cold through a long afternoon outside.', 'Powder-coated steel, a leak-resistant cap, and a mouth wide enough for ice cubes.', [['Capacity', '750 ml'], ['Cold', '24 hours'], ['Material', 'Stainless steel'], ['Cap', 'Screw, leak resistant']]),
            self::item('nb-lamp', 'Arc Desk Lamp', 'Home', 54, 70, 4.6, 640, 'Deal', null, 'Ships in 2 days', ['1507473885765-e6ed057f782c'], ['Brass', 'Black'], [], 'A dimmable arm lamp that aims a warm pool of light at the page, not the whole room.', 'Weighted base, a cloth cord, and three brightness steps from a single button.', [['Light', 'Warm LED'], ['Dimming', '3 steps'], ['Arm', 'Adjustable'], ['Cord', '1.8 m']]),
            self::item('nb-bag', 'Weekender Leather Bag', 'Fashion', 168, null, 4.7, 388, null, null, 'Ships in 2 days', ['1548036328-c9fa89d128fa', '1590874103328-eac38a683ce7'], ['Tan', 'Black'], [], 'A carry-on weekender in full-grain leather with a shoe pocket and a quiet lining.', 'Fits under a narrow airline seat, with cotton handles that are wrapped for the shoulder.', [['Volume', '32 L'], ['Material', 'Full-grain leather'], ['Strap', 'Detachable'], ['Pocket', 'Shoes']]),
            self::item('nb-speaker', 'Pocket Pulse Speaker', 'Electronics', 79, 99, 4.3, 1022, 'Deal', null, 'Ships tomorrow', ['1608043152269-423dbba4e7e1'], ['Stone', 'Red'], [], 'A palm-size speaker with enough low end for a kitchen and a clip for a pack strap.', 'Pairs quickly, shrugs off a splash, and charges from a USB-C cable you already own.', [['Battery', '12 hours'], ['Resistance', 'Splash'], ['Charge', 'USB-C'], ['Link', 'Bluetooth']]),
            self::item('nb-tee', 'Everyday Cotton Tee', 'Fashion', 32, null, 4.5, 870, 'New', null, 'Ships tomorrow', ['1521572163474-6864f9cf17ab'], ['White', 'Navy', 'Rust'], ['XS', 'S', 'M', 'L', 'XL'], 'A midweight tee with a rib that keeps its shape after washing.', 'Cut straight through the body, with a shoulder seam that sits where a shoulder seam should.', [['Fabric', 'Organic cotton'], ['Weight', '180 gsm'], ['Fit', 'Straight'], ['Care', 'Machine wash']]),
            self::item('nb-skillet', 'Hearth Cast Skillet', 'Kitchen', 58, 74, 4.9, 1904, 'Bestseller', null, 'Ships tomorrow', ['1556909114-f6e7ad7d3136'], ['Seasoned iron'], [], 'A 10-inch skillet, pre-seasoned, with a handle you can actually grip.', 'Moves from stove to oven, holds a sear, and hangs from the helper handle on the far side.', [['Diameter', '10 in'], ['Material', 'Cast iron'], ['Oven', 'To 260°C'], ['Seasoning', 'Ready to cook']]),
            self::item('nb-mat', 'Grounded Yoga Mat', 'Outdoors', 36, null, 4.4, 733, null, null, 'Ships tomorrow', ['1601925260368-ae2f83cf8b7f'], ['Sage', 'Clay'], [], 'A 5 mm mat with a tacky top that stays put when your hands get warm.', 'Closed-cell cushion, alignment marks you can ignore, and a strap tucked in the end.', [['Thickness', '5 mm'], ['Length', '180 cm'], ['Surface', 'Tacky'], ['Carry', 'Strap included']]),
            self::item('nb-keyboard', 'Keyline Mechanical Keyboard', 'Electronics', 119, 149, 4.6, 501, 'Deal', null, 'Ships in 2 days', ['1511467687858-23d96c32e4ae'], ['Fog', 'Carbon'], [], 'A compact mechanical board with a soft typing sound and a cable you can swap.', 'Hot-swap sockets, a gentle incline, and keycaps that do not shine after a month.', [['Layout', '75%'], ['Switch', 'Tactile'], ['Connection', 'USB-C'], ['Keycaps', 'PBT']]),
            self::item('nb-candle', 'Cedar Candle Trio', 'Home', 29, null, 4.2, 266, 'New', 0, 'Back soon', ['1572726729207-a78d6feb18d7'], ['Cedar', 'Fig', 'Linen'], [], 'Three small soy candles: cedar, fig leaf, and clean linen.', 'Cotton wicks and glass you can wash and reuse. This set is currently sold out in the demo.', [['Wax', 'Soy blend'], ['Burn', 'About 22 hours each'], ['Count', '3'], ['Vessel', 'Reusable glass']]),
            self::item('nb-shades', 'Solstice Sunglasses', 'Fashion', 68, null, 4.5, 944, null, null, 'Ships tomorrow', ['1511499767150-a48a237f0083'], ['Tortoise', 'Black'], [], 'Lightweight acetate frames with polarized lenses and a case that survives a bag.', 'Spring hinges, a nose bridge that does not pinch, and lenses that do not tint the world brown.', [['Lens', 'Polarized'], ['Frame', 'Acetate'], ['UV', 'UV400'], ['Case', 'Hard shell']]),
            self::item('nb-coffee', 'Dawn Roast Coffee', 'Grocery', 18, 24, 4.8, 3200, 'Bestseller', null, 'Ships tomorrow', ['1447933601403-0c6688de566e', '1495474472287-4d71bcdd2085'], [], [], 'A one-kilo bag of medium roast, chocolate and orange peel, ground for a drip brewer.', 'Roasted in small batches and sealed with a valve so the bag does not balloon in the pantry.', [['Weight', '1 kg'], ['Roast', 'Medium'], ['Grind', 'Drip'], ['Notes', 'Cocoa, orange peel']]),
            self::item('nb-planter', 'Clay Planter Pair', 'Home', 34, null, 4.3, 190, 'New', 4, 'Ships in 2 days', ['1485955900006-10f4d324d411'], ['Sand'], [], 'Two drainage-ready clay pots with saucers, sized for a windowsill herb and a trailing pothos.', 'Unglazed outside, glazed inside, so water leaves through the hole and not the desk.', [['Count', '2 pots, 2 saucers'], ['Widths', '12 cm and 16 cm'], ['Material', 'Clay'], ['Drainage', 'Yes']]),
            self::item('nb-serum', 'Calm Serum Set', 'Beauty', 42, 54, 4.6, 610, 'Deal', null, 'Ships tomorrow', ['1556228720-195a672e8a03'], [], [], 'A short routine: a light serum in the morning and a richer one at night.', 'Fragrance-free, packaged in glass, with a dropper that does not dump half the bottle.', [['Count', '2 × 30 ml'], ['Use', 'Morning and night'], ['Scent', 'Fragrance free'], ['Bottle', 'Glass']]),
            self::item('nb-journal', 'Fieldcloth Journal', 'Stationery', 16, null, 4.7, 280, null, null, 'Ships tomorrow', ['1512820790803-83ca734da794'], ['Olive', 'Navy'], [], 'A cloth-covered notebook with paper that takes a fountain pen without blooming.', '192 pages, a ribbon marker, and a pocket in the back for a receipt or a ticket.', [['Pages', '192'], ['Paper', '90 gsm'], ['Cover', 'Cloth'], ['Lay-flat', 'Sewn binding']]),
        ];
    }

    /**
     * @param  array<int, string>  $images
     * @param  array<int, string>  $colors
     * @param  array<int, string>  $sizes
     * @param  array<int, array{0: string, 1: string}>  $specs
     * @return array<string, mixed>
     */
    private static function item(
        string $id,
        string $name,
        string $category,
        int $price,
        ?int $compare,
        float $rating,
        int $reviews,
        ?string $badge,
        ?int $stock,
        string $ship,
        array $images,
        array $colors,
        array $sizes,
        string $blurb,
        string $details,
        array $specs,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'compare' => $compare,
            'rating' => $rating,
            'reviews' => $reviews,
            'badge' => $badge,
            'stock' => $stock,
            'ship' => $ship,
            'images' => array_map(fn (string $image) => self::shot($image), $images),
            'colors' => $colors,
            'sizes' => $sizes,
            'blurb' => $blurb,
            'details' => $details,
            'specs' => $specs,
        ];
    }
}
