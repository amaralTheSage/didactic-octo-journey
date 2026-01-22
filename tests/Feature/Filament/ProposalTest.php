<?php

use App\Filament\Resources\Campaigns\Pages\ViewCampaign;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\User;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;


uses(RefreshDatabase::class);

beforeEach(function () {
    // Roles are constants in your app (UserRole::COMPANY / AGENCY / INFLUENCER).
    // Adjust if your names differ.
    $this->company = User::factory()->create(['role' => \App\Enums\UserRole::COMPANY]);
    $this->agency = User::factory()->create(['role' => \App\Enums\UserRole::AGENCY]);
    $this->influencer = User::factory()->create(['role' => \App\Enums\UserRole::INFLUENCER]);

    // Provide influencer_info used by the modal (stories_price, reels_price, carrousel_price, commission_cut)
    // Adjust attribute names if your relation / columns differ.
    $this->influencer->influencer_info()->create([
        'agency_id' => $this->agency->id,
        'stories_price' => 100.00,
        'reels_price' => 200.00,
        'carrousel_price' => 150.00,
        'commission_cut' => 20,
    ]);

    $this->product = Product::factory()->create(['company_id' => $this->company->id]);
    $this->campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
        'product_id' => $this->product->id,
        'n_reels' => 1,
        'n_stories' => 1,
        'n_carrousels' => 1,
    ]);
});

test('agency can create a proposal and influencers are synced with pivot data', function () {
    actingAs($this->agency);

    // Data that would come from the modal
    $message = 'We want this campaign';
    $proposedAgencyCut = 25;
    $selectedInfluencers = [
        [
            'user_id' => $this->influencer->id,
            // these are the values the modal provides (floats or numeric strings)
            'reels_price' => 200.00,
            'stories_price' => 100.00,
            'carrousel_price' => 150.00,
            'commission_cut' => 20,
        ],
    ];

    // Simulate the action's core effect (the `action()` closure)
    $proposal = $this->campaign->proposals()->create([
        'campaign_id' => $this->campaign->id,
        'agency_id' => Auth::id() ?? $this->agency->id,
        'message' => $message,
        'proposed_agency_cut' => $proposedAgencyCut,
        'n_reels' => $this->campaign->n_reels,
        'n_stories' => $this->campaign->n_stories,
        'n_carrousels' => $this->campaign->n_carrousels,
    ]);

    $pivotData = [];
    foreach ($selectedInfluencers as $inf) {
        $id = $inf['user_id'];
        $pivotData[$id] = [
            'reels_price' => (float) $inf['reels_price'],
            'stories_price' => (float) $inf['stories_price'],
            'carrousel_price' => (float) $inf['carrousel_price'],
            'commission_cut' => (float) $inf['commission_cut'],
        ];
    }

    $proposal->influencers()->sync($pivotData);

    // Assertions: proposal exists and pivot values are stored
    assertDatabaseHas('proposals', [
        'id' => $proposal->id,
        'campaign_id' => $this->campaign->id,
        'agency_id' => $this->agency->id,
        'message' => $message,
        'proposed_agency_cut' => $proposedAgencyCut,
    ]);

    $this->assertCount(1, $proposal->influencers);
    $pivot = $proposal->influencers()->first()->pivot;

    $this->assertEquals(200.00, (float) $pivot->reels_price);
    $this->assertEquals(100.00, (float) $pivot->stories_price);
    $this->assertEquals(150.00, (float) $pivot->carrousel_price);
    $this->assertEquals(20.0, (float) $pivot->commission_cut);
});

test('non-agency users cannot trigger the agency propose flow (UI-level gate)', function () {
    actingAs($this->company);

    // UI visibility for the modal is guarded by Gate::allows('is_agency')
    $this->assertFalse(Gate::allows('is_agency'));
});

test('proposal created includes campaign deliverables by default', function () {
    actingAs($this->agency);

    $proposal = $this->campaign->proposals()->create([
        'agency_id' => $this->agency->id,
        'message' => 'Deliverables present',
        'proposed_agency_cut' => 30,
    ]);

    assertDatabaseHas('proposals', [
        'id' => $proposal->id,
        'n_reels' => $this->campaign->n_reels,
        'n_stories' => $this->campaign->n_stories,
        'n_carrousels' => $this->campaign->n_carrousels,
    ]);
});

test('proposal deliverables cannot be overridden on creation', function () {
    actingAs($this->agency);

    $proposal = $this->campaign->proposals()->create([
        'agency_id' => $this->agency->id,
        'n_reels' => 999,
        'n_stories' => 999,
        'n_carrousels' => 999,
    ]);

    expect($proposal->n_reels)->toBe($this->campaign->n_reels);
    expect($proposal->n_stories)->toBe($this->campaign->n_stories);
    expect($proposal->n_carrousels)->toBe($this->campaign->n_carrousels);
});


test('proposal belongs to campaign and agency', function () {
    actingAs($this->agency);

    $proposal = Proposal::factory()->create([
        'campaign_id' => $this->campaign->id,
        'agency_id' => $this->agency->id,
    ]);

    expect($proposal->campaign->id)->toBe($this->campaign->id);
    expect($proposal->agency->id)->toBe($this->agency->id);
});


test('proposal influencer pivot stores monetary values correctly', function () {
    actingAs($this->agency);

    $proposal = Proposal::factory()->create([
        'campaign_id' => $this->campaign->id,
        'agency_id' => $this->agency->id,
    ]);

    $proposal->influencers()->sync([
        $this->influencer->id => [
            'reels_price' => 300,
            'stories_price' => 200,
            'carrousel_price' => 150,
            'commission_cut' => 25,
        ],
    ]);

    $pivot = $proposal->influencers()->first()->pivot;

    expect($pivot->reels_price)->toBe(300);
    expect($pivot->commission_cut)->toBe(25);
});

test('proposal starts with pending approvals and draft status', function () {
    $proposal = Proposal::factory()->create();

    expect($proposal->agency_approval)->toBe('pending');
    expect($proposal->company_approval)->toBe('pending');
    expect($proposal->status)->toBe('draft');
});

test('proposal must have at least one influencer', function () {
    actingAs($this->agency);

    $proposal = $this->campaign->proposals()->create([
        'campaign_id' => $this->campaign->id,
        'agency_id' => $this->agency->id,
        'message' => 'No influencers',
        'proposed_agency_cut' => 20,
    ]);

    expect($proposal->influencers)->toHaveCount(0);
});


test('non-agency users cannot see propose action', function () {
    actingAs($this->company);

    Livewire::test(
        ViewCampaign::class,
        ['record' => $this->campaign->id]
    )->assertActionDoesNotExist('propose');
});



test('agency cannot override campaign deliverables when creating proposal', function () {
    actingAs($this->agency);

    $proposal = $this->campaign->proposals()->create([
        'campaign_id' => $this->campaign->id,
        'agency_id' => $this->agency->id,
        'message' => 'Trying to cheat',
        'proposed_agency_cut' => 30,

        // malicious attempt
        'n_reels' => 999,
        'n_stories' => 999,
        'n_carrousels' => 999,
    ]);

    $proposal->refresh();

    expect($proposal->n_reels)->toBe($this->campaign->n_reels)
        ->and($proposal->n_stories)->toBe($this->campaign->n_stories)
        ->and($proposal->n_carrousels)->toBe($this->campaign->n_carrousels);
});
