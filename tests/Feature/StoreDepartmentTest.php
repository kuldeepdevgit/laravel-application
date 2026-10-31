<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreDepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_lists_active_admin_categories(): void
    {
        Category::factory()->create([
            'name' => 'Organic Tea',
            'slug' => 'organic-tea',
            'is_active' => true,
            'is_featured' => true,
        ]);
        Category::factory()->create([
            'name' => 'Plain Spices',
            'slug' => 'plain-spices',
            'is_active' => true,
            'is_featured' => false,
        ]);
        Category::factory()->create([
            'name' => 'Hidden Honey',
            'slug' => 'hidden-honey',
            'is_active' => false,
            'is_featured' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Shop by department')
            ->assertSee('Organic Tea')
            ->assertSee(route('shop', ['cat' => 'organic-tea']), false)
            ->assertDontSee('Plain Spices')
            ->assertDontSee('Hidden Honey');
    }

    public function test_a_department_link_opens_that_category(): void
    {
        Category::factory()->create([
            'name' => 'Organic Tea',
            'slug' => 'organic-tea',
            'is_active' => true,
        ]);

        $this->get('/shop?cat=organic-tea')
            ->assertOk()
            ->assertSee('Organic Tea');
    }
}
