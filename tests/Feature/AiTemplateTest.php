<?php

namespace Tests\Feature;

use Tests\TestCase;

class AiTemplateTest extends TestCase
{
    public function test_the_ai_home_page_introduces_keel(): void
    {
        $this->get('/ai')
            ->assertOk()
            ->assertSee('Introducing AI Design Tool')
            ->assertSee('Keel');
    }

    public function test_pricing_lists_the_studio_plan(): void
    {
        $this->get('/ai/pricing')
            ->assertOk()
            ->assertSee('Studio')
            ->assertSee('$24');
    }

    public function test_the_desk_can_open_with_a_prompt(): void
    {
        $this->get('/ai/workspace?q=Summarize+the+notes')
            ->assertOk()
            ->assertSee('Summarize the notes', false);
    }
}
