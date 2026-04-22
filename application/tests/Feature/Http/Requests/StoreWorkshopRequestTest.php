<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Requests;

use App\Http\Middleware\EnsureAdmin;
use App\Http\Requests\StoreWorkshopRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(StoreWorkshopRequest::class)]
#[UsesClass(User::class)]
#[UsesClass(EnsureAdmin::class)]
class StoreWorkshopRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function store_validates_required_fields(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/workshops', [])
            ->assertSessionHasErrors(['title', 'description', 'starts_at', 'ends_at', 'capacity']);
    }
}
