<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin/companies')->assertRedirect('/login');
    }

    public function test_a_member_cannot_open_companies(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/admin/companies')
            ->assertForbidden();
    }

    public function test_the_super_admin_can_open_an_empty_company_page(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/companies')
            ->assertOk()
            ->assertSee('Super Admin')
            ->assertSee('Company')
            ->assertSee('No companies yet')
            ->assertSee('Add company')
            ->assertDontSee('Manage Profile')
            ->assertDontSee('Ref Link');
    }

    public function test_the_super_admin_can_add_a_company_with_a_logo(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->post('/admin/companies', [
                'name' => 'Northwind Traders',
                'description' => 'Regional supplies.',
                'email' => 'hello@northwind.test',
                'phone' => '9876543210',
                'address' => '12 Market Road',
                'website' => 'https://northwind.test',
                'is_active' => '1',
                'logo' => UploadedFile::fake()->image('logo.jpg', 900, 600),
            ])
            ->assertRedirect('/admin/companies')
            ->assertSessionHas('status', 'Company added.');

        $company = Company::query()->where('name', 'Northwind Traders')->first();

        $this->assertNotNull($company);
        $this->assertSame('northwind-traders', $company->slug);
        $this->assertSame('Regional supplies.', $company->description);
        $this->assertSame('hello@northwind.test', $company->email);
        $this->assertSame('9876543210', $company->phone);
        $this->assertSame('12 Market Road', $company->address);
        $this->assertSame('https://northwind.test', $company->website);
        $this->assertTrue($company->is_active);
        Storage::disk('public')->assertExists($company->logo);

        $this->actingAs($user)
            ->get('/admin/companies')
            ->assertOk()
            ->assertSee('Northwind Traders')
            ->assertSee('Regional supplies.')
            ->assertSee('hello@northwind.test')
            ->assertSee('9876543210')
            ->assertSee('Active');
    }

    public function test_a_company_can_be_added_without_a_logo(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->post('/admin/companies', [
                'name' => 'Contoso',
                'description' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'website' => '',
                'is_active' => '0',
            ])
            ->assertRedirect('/admin/companies')
            ->assertSessionHas('status', 'Company added.');

        $company = Company::query()->where('name', 'Contoso')->first();

        $this->assertNotNull($company);
        $this->assertNull($company->description);
        $this->assertNull($company->email);
        $this->assertNull($company->logo);
        $this->assertFalse($company->is_active);
    }

    public function test_a_company_needs_a_name(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/companies/create')
            ->post('/admin/companies', [
                'name' => '',
                'description' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/companies/create')
            ->assertSessionHasErrors('name');

        $this->assertSame(0, Company::query()->count());
    }

    public function test_contact_details_must_be_valid(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/companies/create')
            ->post('/admin/companies', [
                'name' => 'Northwind',
                'email' => 'not-an-email',
                'website' => 'northwind.test',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/companies/create')
            ->assertSessionHasErrors(['email', 'website']);

        $this->assertSame(0, Company::query()->count());
    }

    public function test_a_company_name_must_be_unique(): void
    {
        Company::factory()->create(['name' => 'Northwind Traders']);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->from('/admin/companies/create')
            ->post('/admin/companies', [
                'name' => 'Northwind Traders',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/companies/create')
            ->assertSessionHasErrors('name');
    }

    public function test_the_super_admin_can_edit_a_company_and_replace_its_logo(): void
    {
        Storage::disk('public')->put('companies/old.jpg', 'old-logo');
        $company = Company::factory()->create([
            'name' => 'Northwind Traders',
            'slug' => 'northwind-traders',
            'description' => 'Old copy',
            'email' => 'old@northwind.test',
            'logo' => 'companies/old.jpg',
            'is_active' => true,
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/companies/'.$company->id.'/edit')
            ->assertOk()
            ->assertSee('Edit company')
            ->assertSee('value="Northwind Traders"', false)
            ->assertSee('Current logo')
            ->assertSee('Crop logo');

        $this->actingAs($user)
            ->put('/admin/companies/'.$company->id, [
                'name' => 'Northwind Goods',
                'description' => 'Updated copy',
                'email' => 'hello@northwind.test',
                'phone' => '9123456780',
                'address' => '8 Harbor Lane',
                'website' => 'https://goods.northwind.test',
                'is_active' => '0',
                'logo' => UploadedFile::fake()->image('logo.png', 700, 700),
            ])
            ->assertRedirect('/admin/companies')
            ->assertSessionHas('status', 'Company saved.');

        $company->refresh();

        $this->assertSame('Northwind Goods', $company->name);
        $this->assertSame('northwind-goods', $company->slug);
        $this->assertSame('Updated copy', $company->description);
        $this->assertSame('hello@northwind.test', $company->email);
        $this->assertSame('9123456780', $company->phone);
        $this->assertSame('8 Harbor Lane', $company->address);
        $this->assertSame('https://goods.northwind.test', $company->website);
        $this->assertFalse($company->is_active);
        $this->assertNotSame('companies/old.jpg', $company->logo);
        Storage::disk('public')->assertMissing('companies/old.jpg');
        Storage::disk('public')->assertExists($company->logo);
    }

    public function test_editing_can_keep_the_current_logo(): void
    {
        Storage::disk('public')->put('companies/keep.jpg', 'keep-logo');
        $company = Company::factory()->create([
            'name' => 'Contoso',
            'logo' => 'companies/keep.jpg',
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->put('/admin/companies/'.$company->id, [
                'name' => 'Contoso Ltd',
                'description' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'website' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/companies');

        $company->refresh();

        $this->assertSame('Contoso Ltd', $company->name);
        $this->assertNull($company->description);
        $this->assertNull($company->email);
        $this->assertSame('companies/keep.jpg', $company->logo);
        Storage::disk('public')->assertExists('companies/keep.jpg');
    }

    public function test_the_super_admin_can_delete_a_company_and_its_logo(): void
    {
        Storage::disk('public')->put('companies/gone.jpg', 'gone-logo');
        $company = Company::factory()->create([
            'name' => 'Fabrikam',
            'logo' => 'companies/gone.jpg',
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->delete('/admin/companies/'.$company->id)
            ->assertRedirect('/admin/companies')
            ->assertSessionHas('status', 'Company deleted.');

        $this->assertModelMissing($company);
        Storage::disk('public')->assertMissing('companies/gone.jpg');
    }

    public function test_search_filters_companies_by_name_or_email(): void
    {
        Company::factory()->create([
            'name' => 'Northwind Traders',
            'email' => 'hello@northwind.test',
        ]);
        Company::factory()->create([
            'name' => 'Contoso',
            'email' => 'hi@contoso.test',
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/admin/companies?q=Contoso')
            ->assertOk()
            ->assertSee('Contoso')
            ->assertDontSee('Northwind Traders');

        $this->actingAs($user)
            ->get('/admin/companies?q=hello@northwind.test')
            ->assertOk()
            ->assertSee('Northwind Traders')
            ->assertDontSee('Contoso');
    }
}
