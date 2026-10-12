<?php

namespace Tests\Feature;

use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BootstrapTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_bootstrap_pages_render(): void
    {
        $this->get('/bootstrap')
            ->assertOk()
            ->assertSee('Brightpath')
            ->assertSee('A Bootstrap front page you can switch on');

        $this->get('/bootstrap/services')
            ->assertOk()
            ->assertSee('What Brightpath ships');

        $this->get('/bootstrap/contact')
            ->assertOk()
            ->assertSee('Send a note');
    }

    public function test_the_contact_form_validates_and_confirms(): void
    {
        $this->post('/bootstrap/contact', [])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->post('/bootstrap/contact', [
            'name' => 'Avery Chen',
            'email' => 'avery@example.com',
            'message' => 'Hello from the form.',
        ])->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_activating_bootstrap_changes_the_front_page(): void
    {
        $bootstrap = Template::query()->where('key', 'bootstrap')->firstOrFail();

        $this->patch(route('templates.update', $bootstrap))
            ->assertRedirect(route('templates.index'));

        $this->get('/')
            ->assertOk()
            ->assertSee('A Bootstrap front page you can switch on')
            ->assertDontSee('Aero Quiet Headphones');

        $this->assertTrue(Template::query()->where('key', 'bootstrap')->value('is_active'));
        $this->assertFalse(Template::query()->where('key', 'ecommerce')->value('is_active'));
    }
}
