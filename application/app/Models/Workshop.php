<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\WorkshopFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property int $capacity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $confirmed_registrations_count
 * @property-read int $available_seats
 * @property-read Collection<int, WorkshopRegistration> $registrations
 */
#[Fillable(['title', 'description', 'starts_at', 'ends_at', 'capacity'])]
class Workshop extends Model
{
    /** @use HasFactory<WorkshopFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $appends = ['available_seats'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'int',
        ];
    }

    /** @return HasMany<WorkshopRegistration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(WorkshopRegistration::class);
    }

    /**
     * @return Attribute<int, never>
     */
    protected function availableSeats(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->capacity - ($this->confirmed_registrations_count ?? 0),
        );
    }
}
