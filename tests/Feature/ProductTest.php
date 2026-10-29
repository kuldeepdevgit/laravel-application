<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin/products')->assertRedirect('/login');
    }

    public function test_a_member_cannot_open_products(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/admin/products')
            ->assertForbidden();
    }

    public function test_the_super_admin_can_open_an_empty_product_page(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/products')
            ->assertOk()
            ->assertSee('Super Admin')
            ->assertSee('Product')
            ->assertSee('No products yet')
            ->assertSee('Add product')
            ->assertDontSee('Manage Profile')
            ->assertDontSee('Ref Link');
    }

    public function test_the_super_admin_can_add_a_product_with_an_image(): void
    {
        $category = Category::factory()->create(['name' => 'Organic Tea']);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->post('/admin/products', [
                'category_id' => $category->id,
                'name' => 'Assam Leaf Tin',
                'description' => 'Loose leaf for a morning pot.',
                'price' => '129.50',
                'product_bv' => '40.00',
                'compare_price' => '168.00',
                'stock' => '12',
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('tea.jpg', 900, 600),
            ])
            ->assertRedirect('/admin/products')
            ->assertSessionHas('status', 'Product added.');

        $product = Product::query()->where('name', 'Assam Leaf Tin')->first();

        $this->assertNotNull($product);
        $this->assertSame('assam-leaf-tin', $product->slug);
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame('Loose leaf for a morning pot.', $product->description);
        $this->assertSame('129.50', $product->price);
        $this->assertSame('40.00', $product->product_bv);
        $this->assertSame('168.00', $product->compare_price);
        $this->assertSame(12, $product->stock);
        $this->assertTrue($product->is_active);
        Storage::disk('public')->assertExists($product->image);

        $this->actingAs($user)
            ->get('/admin/products')
            ->assertOk()
            ->assertSee('Assam Leaf Tin')
            ->assertSee('Loose leaf for a morning pot.')
            ->assertSee('Organic Tea')
            ->assertSee('₹129.50')
            ->assertSee('₹168.00')
            ->assertSee('BV 40.00')
            ->assertSee('12 in stock')
            ->assertSee('Active');
    }

    public function test_a_product_needs_a_name_price_bv_stock_and_image(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/products/create')
            ->post('/admin/products', [
                'name' => '',
                'description' => '',
                'price' => '',
                'product_bv' => '',
                'stock' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/products/create')
            ->assertSessionHasErrors(['name', 'price', 'product_bv', 'stock', 'image']);

        $this->assertSame(0, Product::query()->count());
    }

    public function test_a_product_name_must_be_unique(): void
    {
        Product::factory()->create(['name' => 'Assam Leaf Tin']);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/products/create')
            ->post('/admin/products', [
                'name' => 'Assam Leaf Tin',
                'description' => '',
                'price' => '20',
                'product_bv' => '5',
                'stock' => '1',
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('tea.jpg'),
            ])
            ->assertRedirect('/admin/products/create')
            ->assertSessionHasErrors('name');
    }

    public function test_the_compare_price_must_be_higher_than_the_selling_price(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/products/create')
            ->post('/admin/products', [
                'name' => 'Assam Leaf Tin',
                'price' => '20',
                'product_bv' => '5',
                'compare_price' => '20',
                'stock' => '1',
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('tea.jpg'),
            ])
            ->assertRedirect('/admin/products/create')
            ->assertSessionHasErrors('compare_price');

        $this->assertSame(0, Product::query()->count());
    }

    public function test_the_category_must_exist(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/products/create')
            ->post('/admin/products', [
                'category_id' => 999,
                'name' => 'Assam Leaf Tin',
                'price' => '20',
                'product_bv' => '5',
                'stock' => '1',
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('tea.jpg'),
            ])
            ->assertRedirect('/admin/products/create')
            ->assertSessionHasErrors('category_id');
    }

    public function test_the_super_admin_can_edit_a_product_and_replace_its_image(): void
    {
        Storage::disk('public')->put('products/old.jpg', 'old-image');
        $category = Category::factory()->create(['name' => 'Organic Tea']);
        $next = Category::factory()->create(['name' => 'Herbal Tea']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Assam Leaf Tin',
            'slug' => 'assam-leaf-tin',
            'description' => 'Old copy',
            'price' => '129.50',
            'product_bv' => '40.00',
            'compare_price' => '168.00',
            'stock' => 12,
            'image' => 'products/old.jpg',
            'is_active' => true,
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/products/'.$product->id.'/edit')
            ->assertOk()
            ->assertSee('Edit product')
            ->assertSee('value="Assam Leaf Tin"', false)
            ->assertSee('value="40.00"', false)
            ->assertSee('Organic Tea')
            ->assertSee('Current image')
            ->assertSee('Crop image');

        $this->actingAs($user)
            ->put('/admin/products/'.$product->id, [
                'category_id' => $next->id,
                'name' => 'Darjeeling Leaf Tin',
                'description' => 'Updated copy',
                'price' => '140',
                'product_bv' => '55.50',
                'compare_price' => '',
                'stock' => '0',
                'is_active' => '0',
                'image' => UploadedFile::fake()->image('darjeeling.png', 700, 700),
            ])
            ->assertRedirect('/admin/products')
            ->assertSessionHas('status', 'Product saved.');

        $product->refresh();

        $this->assertSame('Darjeeling Leaf Tin', $product->name);
        $this->assertSame('darjeeling-leaf-tin', $product->slug);
        $this->assertSame($next->id, $product->category_id);
        $this->assertSame('Updated copy', $product->description);
        $this->assertSame('140.00', $product->price);
        $this->assertSame('55.50', $product->product_bv);
        $this->assertNull($product->compare_price);
        $this->assertSame(0, $product->stock);
        $this->assertFalse($product->is_active);
        $this->assertNotSame('products/old.jpg', $product->image);
        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_editing_can_keep_the_current_image(): void
    {
        Storage::disk('public')->put('products/keep.jpg', 'keep-image');
        $product = Product::factory()->create([
            'name' => 'Spices',
            'image' => 'products/keep.jpg',
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->put('/admin/products/'.$product->id, [
                'category_id' => '',
                'name' => 'Whole Spices',
                'description' => '',
                'price' => '45.00',
                'product_bv' => '10',
                'compare_price' => '',
                'stock' => '4',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/products');

        $product->refresh();

        $this->assertSame('Whole Spices', $product->name);
        $this->assertNull($product->category_id);
        $this->assertNull($product->description);
        $this->assertSame('10.00', $product->product_bv);
        $this->assertNull($product->compare_price);
        $this->assertSame('products/keep.jpg', $product->image);
        Storage::disk('public')->assertExists('products/keep.jpg');
    }

    public function test_the_super_admin_can_delete_a_product_and_its_image(): void
    {
        Storage::disk('public')->put('products/gone.jpg', 'gone-image');
        $product = Product::factory()->create([
            'name' => 'Honey',
            'image' => 'products/gone.jpg',
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->delete('/admin/products/'.$product->id)
            ->assertRedirect('/admin/products')
            ->assertSessionHas('status', 'Product deleted.');

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing('products/gone.jpg');
    }

    public function test_search_filters_products_by_name_and_category(): void
    {
        $tea = Category::factory()->create(['name' => 'Organic Tea']);
        $honey = Category::factory()->create(['name' => 'Raw Honey']);
        Product::factory()->create([
            'name' => 'Assam Leaf Tin',
            'category_id' => $tea->id,
        ]);
        Product::factory()->create([
            'name' => 'Forest Jar',
            'category_id' => $honey->id,
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/products?q=Honey')
            ->assertOk()
            ->assertSee('Forest Jar')
            ->assertDontSee('Assam Leaf Tin');

        $this->actingAs($user)
            ->get('/admin/products?q=Assam')
            ->assertOk()
            ->assertSee('Assam Leaf Tin')
            ->assertDontSee('Forest Jar');
    }
}
