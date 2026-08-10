<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFirm;
use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $firm_id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $ic_number
 * @property string $gender
 * @property Carbon $date_of_birth
 * @property string $phone
 * @property string $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'first_name', 'last_name', 'ic_number', 'gender', 'date_of_birth', 'phone', 'email'])]
class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use BelongsToFirm, HasFactory;

    protected $table = 'lfp_user_profiles';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<UserAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class, 'profile_id');
    }
}
