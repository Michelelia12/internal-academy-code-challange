<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Requests;

use App\Http\Middleware\EnsureAdmin;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(UpdateWorkshopRequest::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
#[UsesClass(EnsureAdmin::class)]
class UpdateWorkshopRequestTest extends TestCase
{
    use RefreshDatabase;

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
