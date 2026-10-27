<?php

namespace Tests\Feature;

use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_front_page_shows_the_active_ecommerce_template(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Green Smart India E Services')
            ->assertSee('images/green-smart-india.png', false)
            ->assertDontSee('Introducing AI Design Tool');
    }

    public function test_activating_the_ai_template_changes_the_front_page(): void
    {
        $ai = Template::query()->where('key', 'ai')->first();

        $this->patch(route('templates.update', $ai))
            ->assertRedirect(route('templates.index'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Introducing AI Design Tool')
            ->assertDontSee('Aero Quiet Headphones');

        $this->assertTrue(Template::query()->where('key', 'ai')->value('is_active'));
        $this->assertFalse(Template::query()->where('key', 'ecommerce')->value('is_active'));
    }
}
