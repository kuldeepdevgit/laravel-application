<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin/categories')->assertRedirect('/login');
    }

    public function test_the_super_admin_can_open_an_empty_category_page(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/categories')
            ->assertOk()
            ->assertSee('Super Admin')
            ->assertSee('Category')
            ->assertSee('No categories yet')
            ->assertSee('Add category')
            ->assertDontSee('Manage Profile')
            ->assertDontSee('Ref Link');
    }

    public function test_the_super_admin_can_add_a_category_with_an_image(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->post('/admin/categories', [
                'name' => 'Organic Tea',
                'description' => 'Leaves and blends.',
                'is_active' => '1',
                'is_featured' => '1',
                'image' => UploadedFile::fake()->image('tea.jpg', 900, 600),
            ])
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('status', 'Category added.');

        $category = Category::query()->where('name', 'Organic Tea')->first();

        $this->assertNotNull($category);
        $this->assertSame('organic-tea', $category->slug);
        $this->assertSame('Leaves and blends.', $category->description);
        $this->assertTrue($category->is_active);
        $this->assertTrue($category->is_featured);
        $this->assertStringStartsWith('uploads/'.now()->format('Y/m').'/category/category-img/', $category->image);
        $this->assertFileExists(public_path($category->image));
        $this->assertStringContainsString(
            'uploads/'.now()->format('Y/m').'/category/category-img/',
            $category->imageUrl() ?? '',
        );

        $this->actingAs($user)
            ->get('/admin/categories')
            ->assertOk()
            ->assertSee('Organic Tea')
            ->assertSee('Leaves and blends.')
            ->assertSee('Active');

        $image = $category->image;
        $category->delete();
        $this->assertFileDoesNotExist(public_path($image));
    }

    public function test_a_category_needs_a_name_and_an_image(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/categories/create')
            ->post('/admin/categories', [
                'name' => '',
                'description' => '',
                'is_active' => '1',
                'is_featured' => '1',
            ])
            ->assertRedirect('/admin/categories/create')
            ->assertSessionHasErrors(['name', 'image']);

        $this->assertSame(0, Category::query()->count());
    }

    public function test_a_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Organic Tea']);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/categories/create')
            ->post('/admin/categories', [
                'name' => 'Organic Tea',
                'description' => '',
                'is_active' => '1',
                'is_featured' => '1',
                'image' => UploadedFile::fake()->image('tea.jpg'),
            ])
            ->assertRedirect('/admin/categories/create')
            ->assertSessionHasErrors('name');
    }

    public function test_the_super_admin_can_edit_a_category_and_replace_its_image(): void
    {
        Storage::disk('public')->put('categories/old.jpg', 'old-image');
        $category = Category::factory()->create([
            'name' => 'Organic Tea',
            'slug' => 'organic-tea',
            'description' => 'Old copy',
            'image' => 'categories/old.jpg',
            'is_active' => true,
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/categories/'.$category->id.'/edit')
            ->assertOk()
            ->assertSee('Edit category')
            ->assertSee('Feature')
            ->assertSee('value="Organic Tea"', false)
            ->assertSee('Current image')
            ->assertSee('Crop image');

        $this->actingAs($user)
            ->put('/admin/categories/'.$category->id, [
                'name' => 'Herbal Tea',
                'description' => 'Updated copy',
                'is_active' => '0',
                'is_featured' => '0',
                'image' => UploadedFile::fake()->image('herbal.png', 700, 700),
            ])
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('status', 'Category saved.');

        $category->refresh();

        $this->assertSame('Herbal Tea', $category->name);
        $this->assertSame('herbal-tea', $category->slug);
        $this->assertSame('Updated copy', $category->description);
        $this->assertFalse($category->is_active);
        $this->assertFalse($category->is_featured);
        $this->assertNotSame('categories/old.jpg', $category->image);
        $this->assertStringStartsWith('uploads/'.now()->format('Y/m').'/category/category-img/', $category->image);
        $this->assertFileExists(public_path($category->image));
        Storage::disk('public')->assertMissing('categories/old.jpg');

        $image = $category->image;
        $category->delete();
        $this->assertFileDoesNotExist(public_path($image));
    }

    public function test_replacing_a_category_image_deletes_the_previous_upload(): void
    {
        $previous = Category::storeImage(UploadedFile::fake()->image('old.jpg', 400, 267));
        $category = Category::factory()->create([
            'name' => 'Organic Tea',
            'image' => $previous,
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->put('/admin/categories/'.$category->id, [
                'name' => 'Herbal Tea',
                'description' => '',
                'is_active' => '1',
                'is_featured' => '1',
                'image' => UploadedFile::fake()->image('new.jpg', 800, 500),
            ])
            ->assertRedirect('/admin/categories');

        $category->refresh();

        $this->assertNotSame($previous, $category->image);
        $this->assertFileDoesNotExist(public_path($previous));
        $this->assertFileExists(public_path($category->image));

        $category->delete();
        $this->assertFileDoesNotExist(public_path($category->image));
    }

    public function test_editing_can_keep_the_current_image(): void
    {
        Storage::disk('public')->put('categories/keep.jpg', 'keep-image');
        $category = Category::factory()->create([
            'name' => 'Spices',
            'image' => 'categories/keep.jpg',
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->put('/admin/categories/'.$category->id, [
                'name' => 'Whole Spices',
                'description' => '',
                'is_active' => '1',
                'is_featured' => '1',
            ])
            ->assertRedirect('/admin/categories');

        $category->refresh();

        $this->assertSame('Whole Spices', $category->name);
        $this->assertNull($category->description);
        $this->assertSame('categories/keep.jpg', $category->image);
        Storage::disk('public')->assertExists('categories/keep.jpg');
    }

    public function test_the_super_admin_can_delete_a_category_and_its_image(): void
    {
        Storage::disk('public')->put('categories/gone.jpg', 'gone-image');
        $category = Category::factory()->create([
            'name' => 'Honey',
            'image' => 'categories/gone.jpg',
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->delete('/admin/categories/'.$category->id)
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('status', 'Category deleted.');

        $this->assertModelMissing($category);
        Storage::disk('public')->assertMissing('categories/gone.jpg');
    }

    public function test_search_filters_categories_by_name(): void
    {
        Category::factory()->create(['name' => 'Organic Tea']);
        Category::factory()->create(['name' => 'Raw Honey']);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/categories?q=Honey')
            ->assertOk()
            ->assertSee('Raw Honey')
            ->assertDontSee('Organic Tea');
    }
}
