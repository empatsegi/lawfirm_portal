<?php

namespace App\Models;

use Database\Factories\FirmFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * The tenant root. This model is deliberately NOT firm-scoped — it is the thing
 * being scoped to, so it must never use the BelongsToFirm trait.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $registration_no
 * @property string|null $email
 * @property string|null $phone
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'registration_no', 'email', 'phone', 'is_active'])]
class Firm extends Model
{
    /** @use HasFactory<FirmFactory> */
    use HasFactory;

    protected $table = 'lfp_firms';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
