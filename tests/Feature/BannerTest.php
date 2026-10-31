<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_the_super_admin_can_open_an_empty_banner_page(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/banners')
            ->assertOk()
            ->assertSee('Banner')
            ->assertSee('No banners yet')
            ->assertSee('1800 × 1201')
            ->assertSee('Add banner');
    }

    public function test_the_super_admin_can_add_a_banner_with_an_image(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->post('/admin/banners', [
                'eyebrow' => 'New this week',
                'title' => 'The weekend edit',
                'description' => 'Throws and lamps for a slower morning.',
                'button_label' => 'Shop home goods',
                'button_url' => '/shop',
                'is_active' => '1',
                'sort_order' => '1',
                'image' => UploadedFile::fake()->image('hero.jpg', 1800, 1201),
            ])
            ->assertRedirect('/admin/banners')
            ->assertSessionHas('status', 'Banner added.');

        $banner = Banner::query()->where('title', 'The weekend edit')->first();

        $this->assertNotNull($banner);
        $this->assertSame('New this week', $banner->eyebrow);
        $this->assertSame('Throws and lamps for a slower morning.', $banner->description);
        $this->assertSame('Shop home goods', $banner->button_label);
        $this->assertSame('/shop', $banner->button_url);
        $this->assertTrue($banner->is_active);
        $this->assertSame(1, $banner->sort_order);
        $this->assertStringStartsWith('uploads/'.now()->format('Y/m').'/banner/banner-img/', $banner->image);
        $this->assertFileExists(public_path($banner->image));

        $this->actingAs($user)
            ->get('/admin/banners')
            ->assertOk()
            ->assertSee('The weekend edit')
            ->assertSee('New this week')
            ->assertSee('Active');

        $image = $banner->image;
        $banner->delete();
        $this->assertFileDoesNotExist(public_path($image));
    }

    public function test_a_banner_needs_a_headline_and_an_image(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/banners/create')
            ->post('/admin/banners', [
                'title' => '',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect('/admin/banners/create')
            ->assertSessionHasErrors(['title', 'image']);

        $this->assertSame(0, Banner::query()->count());
    }

    public function test_a_button_needs_both_a_label_and_a_safe_link(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/banners/create')
            ->post('/admin/banners', [
                'title' => 'Weekend edit',
                'button_label' => 'Shop now',
                'button_url' => 'javascript:alert(1)',
                'is_active' => '1',
                'sort_order' => '1',
                'image' => UploadedFile::fake()->image('hero.jpg', 1800, 1201),
            ])
            ->assertRedirect('/admin/banners/create')
            ->assertSessionHasErrors('button_url');

        $this->assertSame(0, Banner::query()->count());
    }

    public function test_the_super_admin_can_edit_a_banner_and_replace_its_image(): void
    {
        $previous = Banner::storeImage(UploadedFile::fake()->image('old.jpg', 1800, 1201));
        $banner = Banner::factory()->create([
            'title' => 'Old headline',
            'image' => $previous,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/banners/'.$banner->id.'/edit')
            ->assertOk()
            ->assertSee('Edit banner')
            ->assertSee('1800 × 1201')
            ->assertSee('value="Old headline"', false)
            ->assertSee('Current image')
            ->assertSee('Crop image');

        $this->actingAs($user)
            ->put('/admin/banners/'.$banner->id, [
                'eyebrow' => 'Out the door',
                'title' => 'Gear that travels',
                'description' => 'A bag that fits under the seat.',
                'button_label' => 'Shop outdoors',
                'button_url' => 'https://example.com/outdoors',
                'is_active' => '0',
                'sort_order' => '4',
                'image' => UploadedFile::fake()->image('new.png', 2000, 1400),
            ])
            ->assertRedirect('/admin/banners')
            ->assertSessionHas('status', 'Banner saved.');

        $banner->refresh();

        $this->assertSame('Gear that travels', $banner->title);
        $this->assertSame('Out the door', $banner->eyebrow);
        $this->assertFalse($banner->is_active);
        $this->assertSame(4, $banner->sort_order);
        $this->assertSame('https://example.com/outdoors', $banner->button_url);
        $this->assertNotSame($previous, $banner->image);
        $this->assertFileDoesNotExist(public_path($previous));
        $this->assertFileExists(public_path($banner->image));

        $image = $banner->image;
        $banner->delete();
        $this->assertFileDoesNotExist(public_path($image));
    }

    public function test_editing_can_keep_the_current_image(): void
    {
        $image = Banner::storeImage(UploadedFile::fake()->image('keep.jpg', 1800, 1201));
        $banner = Banner::factory()->create([
            'title' => 'Keep this',
            'image' => $image,
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->put('/admin/banners/'.$banner->id, [
                'title' => 'Still here',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect('/admin/banners');

        $banner->refresh();

        $this->assertSame('Still here', $banner->title);
        $this->assertSame($image, $banner->image);
        $this->assertFileExists(public_path($image));

        $banner->delete();
        $this->assertFileDoesNotExist(public_path($image));
    }

    public function test_the_home_page_uses_active_banners_in_order(): void
    {
        $firstImage = Banner::storeImage(UploadedFile::fake()->image('first.jpg', 1800, 1201));
        $secondImage = Banner::storeImage(UploadedFile::fake()->image('second.jpg', 1800, 1201));
        Banner::factory()->create([
            'title' => 'Second slide',
            'eyebrow' => 'Later',
            'button_label' => 'Shop later',
            'button_url' => '/shop',
            'image' => $secondImage,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        Banner::factory()->create([
            'title' => 'Hidden slide',
            'image' => null,
            'is_active' => false,
            'sort_order' => 0,
        ]);
        Banner::factory()->create([
            'title' => 'First slide',
            'eyebrow' => 'Now',
            'description' => 'Shown first.',
            'button_label' => 'Shop now',
            'button_url' => '/shop',
            'image' => $firstImage,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['First slide', 'Second slide'])
            ->assertSee('Now')
            ->assertSee('Shown first.')
            ->assertSee('Shop now')
            ->assertSee($firstImage, false)
            ->assertDontSee('Hidden slide')
            ->assertDontSee('The weekend edit');

        Banner::query()->each(fn (Banner $banner) => $banner->delete());
    }

    public function test_inactive_banners_hide_the_default_slides(): void
    {
        Banner::factory()->create([
            'title' => 'Paused slide',
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Paused slide')
            ->assertDontSee('The weekend edit');
    }

    public function test_the_home_page_keeps_the_default_slides_until_a_banner_exists(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('The weekend edit')
            ->assertSee('Gear that travels');
    }
}
