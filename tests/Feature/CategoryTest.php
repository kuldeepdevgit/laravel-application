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
        $this->get('/dashboard/categories')->assertRedirect('/login');
    }

    public function test_a_member_can_open_an_empty_category_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard/categories')
            ->assertOk()
            ->assertSee('Category')
            ->assertSee('No categories yet')
            ->assertSee('Add category');
    }

    public function test_a_member_can_add_a_category_with_an_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/dashboard/categories', [
                'name' => 'Organic Tea',
                'description' => 'Leaves and blends.',
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('tea.jpg', 900, 600),
            ])
            ->assertRedirect('/dashboard/categories')
            ->assertSessionHas('status', 'Category added.');

        $category = Category::query()->where('name', 'Organic Tea')->first();

        $this->assertNotNull($category);
        $this->assertSame('organic-tea', $category->slug);
        $this->assertSame('Leaves and blends.', $category->description);
        $this->assertTrue($category->is_active);
        Storage::disk('public')->assertExists($category->image);

        $this->actingAs($user)
            ->get('/dashboard/categories')
            ->assertOk()
            ->assertSee('Organic Tea')
            ->assertSee('Leaves and blends.')
            ->assertSee('Active');
    }

    public function test_a_category_needs_a_name_and_an_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/dashboard/categories/create')
            ->post('/dashboard/categories', [
                'name' => '',
                'description' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/dashboard/categories/create')
            ->assertSessionHasErrors(['name', 'image']);

        $this->assertSame(0, Category::query()->count());
    }

    public function test_a_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Organic Tea']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/dashboard/categories/create')
            ->post('/dashboard/categories', [
                'name' => 'Organic Tea',
                'description' => '',
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('tea.jpg'),
            ])
            ->assertRedirect('/dashboard/categories/create')
            ->assertSessionHasErrors('name');
    }

    public function test_a_member_can_edit_a_category_and_replace_its_image(): void
    {
        Storage::disk('public')->put('categories/old.jpg', 'old-image');
        $category = Category::factory()->create([
            'name' => 'Organic Tea',
            'slug' => 'organic-tea',
            'description' => 'Old copy',
            'image' => 'categories/old.jpg',
            'is_active' => true,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard/categories/'.$category->id.'/edit')
            ->assertOk()
            ->assertSee('Edit category')
            ->assertSee('value="Organic Tea"', false)
            ->assertSee('Current image')
            ->assertSee('Crop image');

        $this->actingAs($user)
            ->put('/dashboard/categories/'.$category->id, [
                'name' => 'Herbal Tea',
                'description' => 'Updated copy',
                'is_active' => '0',
                'image' => UploadedFile::fake()->image('herbal.png', 700, 700),
            ])
            ->assertRedirect('/dashboard/categories')
            ->assertSessionHas('status', 'Category saved.');

        $category->refresh();

        $this->assertSame('Herbal Tea', $category->name);
        $this->assertSame('herbal-tea', $category->slug);
        $this->assertSame('Updated copy', $category->description);
        $this->assertFalse($category->is_active);
        $this->assertNotSame('categories/old.jpg', $category->image);
        Storage::disk('public')->assertMissing('categories/old.jpg');
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_editing_can_keep_the_current_image(): void
    {
        Storage::disk('public')->put('categories/keep.jpg', 'keep-image');
        $category = Category::factory()->create([
            'name' => 'Spices',
            'image' => 'categories/keep.jpg',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/dashboard/categories/'.$category->id, [
                'name' => 'Whole Spices',
                'description' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/dashboard/categories');

        $category->refresh();

        $this->assertSame('Whole Spices', $category->name);
        $this->assertNull($category->description);
        $this->assertSame('categories/keep.jpg', $category->image);
        Storage::disk('public')->assertExists('categories/keep.jpg');
    }

    public function test_a_member_can_delete_a_category_and_its_image(): void
    {
        Storage::disk('public')->put('categories/gone.jpg', 'gone-image');
        $category = Category::factory()->create([
            'name' => 'Honey',
            'image' => 'categories/gone.jpg',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/dashboard/categories/'.$category->id)
            ->assertRedirect('/dashboard/categories')
            ->assertSessionHas('status', 'Category deleted.');

        $this->assertModelMissing($category);
        Storage::disk('public')->assertMissing('categories/gone.jpg');
    }

    public function test_search_filters_categories_by_name(): void
    {
        Category::factory()->create(['name' => 'Organic Tea']);
        Category::factory()->create(['name' => 'Raw Honey']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard/categories?q=Honey')
            ->assertOk()
            ->assertSee('Raw Honey')
            ->assertDontSee('Organic Tea');
    }
}
