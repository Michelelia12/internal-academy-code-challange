<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User $user
 * @property-read Workshop $workshop
 */
#[Fillable(['user_id', 'workshop_id', 'status', 'position'])]
class Registration extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Workshop, $this> */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
}
