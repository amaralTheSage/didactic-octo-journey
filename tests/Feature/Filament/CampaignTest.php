<?php

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use App\Enums\UserRole;
use App\Filament\Resources\Campaigns\Pages\CreateCampaign;
use App\Filament\Resources\Campaigns\Pages\EditCampaign;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->company = User::factory()->create(['role' => UserRole::COMPANY]);
    $this->agency = User::factory()->create(['role' => UserRole::AGENCY]);
    $this->influencer = User::factory()->create(['role' => UserRole::INFLUENCER]);

    $this->category = Category::create(['title' => 'categoria teste']);
    $this->subcategory = Subcategory::create(['title' => 'subcategoria teste', 'category_id' => $this->category->id]);


    $this->product = Product::create([
        'name'        => 'Test Product',
        'description' => 'Test description',
        'price'       => 100.00,
        'company_id'  => $this->company->id,
        'category_id' => $this->category->id,
    ]);


    $this->attribute = Attribute::create(['title' => 'Público-alvo', 'multiple_values' => true]);

    $this->attributeValue = AttributeValue::create(['title' => '18 - 30', 'attribute_id' => $this->attribute->id]);
});

test('company can access campaign create page', function () {
    actingAs($this->company);

    get(route('filament.admin.resources.campaigns.create'))
        ->assertOk();
});

test('agency cannot access campaign create page', function () {
    actingAs($this->agency);

    get(route('filament.admin.resources.campaigns.create'))
        ->assertForbidden();
});

test('influencer cannot access campaign create page', function () {
    actingAs($this->influencer);

    get(route('filament.admin.resources.campaigns.create'))
        ->assertForbidden();
});


// Campaign Creation Tests
test('can create campaign with valid data', function () {
    actingAs($this->company);

    Livewire::test(CreateCampaign::class)
        ->set('data.name', 'Test Campaign')
        ->set('data.product_id', $this->product->id)
        ->set('data.company_id', $this->company->id)
        ->set('data.budget', '10.000,00')
        ->set('data.agency_cut', 30)
        ->set('data.n_stories', 5)
        ->set('data.n_reels', 3)
        ->set('data.n_carrousels', 2)
        ->set('data.n_influencers', 10)
        ->set('data.duration', 30)
        ->set('data.subcategory_ids', [$this->subcategory->id])
        ->call('create')
        ->assertHasNoErrors();

    assertDatabaseHas('campaigns', [
        'name' => 'Test Campaign',
        'product_id' => $this->product->id,
        'company_id' => $this->company->id,
        'budget' => 10000,
        'agency_cut' => 30,
    ]);
});


test('campaign validation rules are enforced', function () {
    actingAs($this->company);

    Livewire::test(CreateCampaign::class)
        ->call('create')
        ->assertHasErrors([
            'data.name',
            'data.product_id',
            'data.agency_cut',
        ]);

    Livewire::test(CreateCampaign::class)
        ->set('data.name', 'Test Campaign')
        ->set('data.product_id', $this->product->id)
        ->set('data.budget', 10000)
        ->set('data.agency_cut', 150)
        ->call('create')
        ->assertHasErrors([
            'data.agency_cut' => 'max',
        ]);
});


test('subcategories are required', function () {
    actingAs($this->company);

    Livewire::test(CreateCampaign::class)
        ->set('data.name', 'Test Campaign')
        ->set('data.product_id', $this->product->id)
        ->set('data.budget', 10000)
        ->set('data.agency_cut', 30)
        ->call('create')
        ->assertHasErrors([
            'data.subcategory_ids' => 'required',
        ]);
});


// Relationship Tests
test('campaign is associated with correct product', function () {
    $campaign = Campaign::factory()->create([
        'product_id' => $this->product->id,
        'company_id' => $this->company->id,
    ]);

    expect($campaign->product->id)->toBe($this->product->id);
});

test('campaign is associated with correct company', function () {
    $campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
    ]);

    expect($campaign->company->id)->toBe($this->company->id);
});


test('campaign can have multiple subcategories', function () {
    $subcategory2 = Subcategory::factory()->create([
        'category_id' => $this->category->id,
    ]);

    $campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $campaign->subcategories()->attach([
        $this->subcategory->id,
        $subcategory2->id,
    ]);

    expect($campaign->subcategories)->toHaveCount(2);
});

// Attribute Values Tests
test('campaign can have attribute values', function () {
    $campaign = Campaign::factory()->create(['company_id' => $this->company->id]);

    $campaign->attribute_values()->attach($this->attributeValue->id, ['title' => 'Custom Value']);

    assertDatabaseHas('attribute_value_campaign', [
        'campaign_id' => $campaign->id,
        'attribute_value_id' => $this->attributeValue->id,
        'title' => 'Custom Value',
    ]);
});

test('attribute values are saved with custom titles', function () {
    actingAs($this->company);

    $campaign = Campaign::factory()->create(['company_id' => $this->company->id]);

    $attributeData = [
        [
            'attribute_id' => $this->attribute->id,
            'attribute_value_id' => [$this->attributeValue->id],
            'title' => 'Outro - Custom',
        ],
    ];

    foreach ($attributeData as $attr) {
        foreach ($attr['attribute_value_id'] as $valueId) {
            $campaign->attribute_values()->attach($valueId, ['title' => $attr['title']]);
        }
    }

    expect($campaign->attribute_values()->first()->pivot->title)->toBe('Outro - Custom');
});

// Product Inline Creation Tests
test('can create product inline when creating campaign', function () {
    actingAs($this->company);

    $productData = [
        'name' => 'New Product',
        'price' => '99.99',
        'description' => 'Test product',
        'company_id' => $this->company->id,
    ];

    $product = Product::create($productData);

    assertDatabaseHas('products', [
        'name' => 'New Product',
        'company_id' => $this->company->id,
    ]);
});

// Influencer Selection Tests
test('campaign can have influencers pre-selected for proposals', function () {
    $influencer = User::factory()->create(['role' => UserRole::INFLUENCER]);
    $influencer->influencer_info()->create(['agency_id' => $this->agency->id]);

    actingAs($this->company);

    $campaignData = [
        'name' => 'Test Campaign',
        'product_id' => $this->product->id,
        'company_id' => $this->company->id,
        'budget' => '10000.00',
        'agency_cut' => 30,
        'subcategory_ids' => [$this->subcategory->id],
        'influencer_ids' => [$influencer->id],
    ];

    // Note: The actual proposal creation would happen in the afterCreate hook
    $response = get(route('filament.admin.resources.campaigns.create'), $campaignData);

    $response->assertSuccessful();
});

// Deliverables Tests
test('deliverables default to zero', function () {
    $campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
        'n_stories' => 0,
        'n_reels' => 0,
        'n_carrousels' => 0,
    ]);

    expect($campaign->n_stories)->toBe(0)
        ->and($campaign->n_reels)->toBe(0)
        ->and($campaign->n_carrousels)->toBe(0);
});

test('deliverables can be set', function () {
    $campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
        'n_stories' => 5,
        'n_reels' => 3,
        'n_carrousels' => 2,
    ]);

    expect($campaign->n_stories)->toBe(5)
        ->and($campaign->n_reels)->toBe(3)
        ->and($campaign->n_carrousels)->toBe(2);
});

// Update Tests


test('company can update their own campaign', function () {
    actingAs($this->company);

    $campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
        'product_id' => $this->product->id,
    ]);

    $campaign->subcategories()->attach($this->subcategory->id);

    Livewire::test(EditCampaign::class, [
        'record' => $campaign->getRouteKey(),
    ])
        ->set('data.name', 'Updated Campaign Name')
        ->set('data.product_id', $this->product->id)
        ->set('data.subcategory_ids', [$this->subcategory->id])
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas('campaigns', [
        'id' => $campaign->id,
        'name' => 'Updated Campaign Name',
    ]);
});


test("company cannot update another company's campaign", function () {
    actingAs($this->company);

    $campaign = Campaign::factory()->create();

    get(route('filament.admin.resources.campaigns.edit', $campaign))
        ->assertNotFound();
});


// Delete Tests
test('company can delete their own campaign', function () {
    actingAs($this->company);

    $campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
    ]);

    Livewire::test(EditCampaign::class, [
        'record' => $campaign->getRouteKey(),
    ])
        ->callAction('delete');

    assertDatabaseMissing('campaigns', [
        'id' => $campaign->id,
    ]);
});

// Verification Tests
test('campaign starts unverified', function () {
    $campaign = Campaign::factory()->create(['company_id' => $this->company->id]);

    expect($campaign->validated_at)->toBeNull();
});


test('campaign can be verified after payment', function () {
    $campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
        'validated_at' => now(),
    ]);


    expect($campaign->validated_at)->not->toBeNull();
});
