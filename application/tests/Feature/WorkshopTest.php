<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\WorkshopController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(WorkshopController::class)]
#[CoversClass(StoreWorkshopRequest::class)]
#[CoversClass(UpdateWorkshopRequest::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
#[UsesClass(EnsureAdmin::class)]
class WorkshopTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $validData = [
        'title' => 'Laravel Workshop',
        'description' => 'An introduction to Laravel.',
        'starts_at' => '2030-06-01 09:00:00',
        'ends_at' => '2030-06-01 11:00:00',
        'capacity' => 20,
    ];

    #[Test]
    public function admin_can_list_workshops(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        Workshop::factory()->count(3)->create();

        $this->actingAs($admin)->get('/workshops')
            ->assertStatus(200)
            ->assertSee('data-page="app"', false)
            ->assertSee('"component":"Workshops\\/Index"', false);
    }

    #[Test]
    public function admin_can_create_a_workshop(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/workshops', $this->validData)
            ->assertRedirect();

        $this->assertDatabaseHas('workshops', ['title' => 'Laravel Workshop']);
    }

    #[Test]
    public function admin_can_update_a_workshop(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        $this->actingAs($admin)
            ->put("/workshops/{$workshop->id}", $this->validData)
            ->assertRedirect();

        $this->assertDatabaseHas('workshops', ['id' => $workshop->id, 'title' => 'Laravel Workshop']);
    }

    #[Test]
    public function admin_can_delete_a_workshop(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        $this->actingAs($admin)->delete("/workshops/{$workshop->id}")->assertRedirect();

        $this->assertDatabaseMissing('workshops', ['id' => $workshop->id]);
    }

    #[Test]
    public function employee_is_blocked_from_all_workshop_routes(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        $this->actingAs($employee)->get('/workshops')->assertStatus(403);
        $this->actingAs($employee)->post('/workshops', $this->validData)->assertStatus(403);
        $this->actingAs($employee)->put("/workshops/{$workshop->id}", $this->validData)->assertStatus(403);
        $this->actingAs($employee)->delete("/workshops/{$workshop->id}")->assertStatus(403);
    }

    #[Test]
    public function guest_is_forbidden_from_accessing_workshop_routes(): void
    {
        $this->get('/workshops')->assertStatus(403);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/workshops', [])
            ->assertSessionHasErrors(['title', 'description', 'starts_at', 'ends_at', 'capacity']);
    }

    #[Test]
    public function update_validates_required_fields(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        $this->actingAs($admin)->put("/workshops/{$workshop->id}", [])
            ->assertSessionHasErrors(['title', 'description', 'starts_at', 'ends_at', 'capacity']);
    }
}
