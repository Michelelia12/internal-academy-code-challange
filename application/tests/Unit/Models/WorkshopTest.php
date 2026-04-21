<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(Workshop::class)]
class WorkshopTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $workshop = new Workshop;

        $this::assertSame(
            ['title', 'description', 'starts_at', 'ends_at', 'capacity'],
            $workshop->getFillable()
        );
    }

    #[Test]
    public function it_casts_starts_at_and_ends_at_to_datetime(): void
    {
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        $this::assertInstanceOf(Carbon::class, $workshop->starts_at);
        $this::assertInstanceOf(Carbon::class, $workshop->ends_at);
    }

    #[Test]
    public function it_casts_capacity_to_int(): void
    {
        $workshop = Workshop::factory()->make(['capacity' => '10']);

        $this::assertSame(10, $workshop->capacity);
    }

    #[Test]
    public function available_seats_equals_capacity_when_no_registrations(): void
    {
        $workshop = Workshop::factory()->make(['capacity' => 15]);
        $workshop->confirmed_registrations_count = 0;

        $this::assertSame(15, $workshop->available_seats);
    }

    #[Test]
    public function available_seats_reflects_partial_registrations(): void
    {
        $workshop = Workshop::factory()->make(['capacity' => 20]);
        $workshop->confirmed_registrations_count = 7;

        $this::assertSame(13, $workshop->available_seats);
    }

    #[Test]
    public function available_seats_is_zero_when_fully_booked(): void
    {
        $workshop = Workshop::factory()->make(['capacity' => 10]);
        $workshop->confirmed_registrations_count = 10;

        $this::assertSame(0, $workshop->available_seats);
    }
}
