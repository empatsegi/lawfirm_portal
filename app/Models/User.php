<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\BelongsToFirm;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $firm_id
 * @property string $username
 * @property int $role
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read UserProfile|null $profile
 * @property-read string $name
 */
#[Fillable(['username', 'role', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToFirm, HasFactory, Notifiable;

    protected $table = 'lfp_users';

    /**
     * Firm isolation on users is enforced through policies and firm-scoped
     * relations rather than a global scope.
     *
     * The session guard re-fetches the authenticated user by id on every
     * request, before any middleware has had a chance to resolve the firm. A
     * global scope here would therefore match nothing and log everyone out.
     * Query users via $firm->users() or User::ofFirm($id) instead.
     */
    protected static function appliesFirmGlobalScope(): bool
    {
        return false;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasOne<UserProfile, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * The name to show a human. There is no name column on lfp_users — the
     * person's name lives on their profile, which not every user has yet, so
     * this falls back to the login handle rather than rendering as blank.
     *
     * Eager load the profile relation wherever this is serialised in bulk.
     *
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(function (): string {
            $profile = $this->profile;

            $full = $profile === null
                ? ''
                : trim("{$profile->first_name} {$profile->last_name}");

            return $full === '' ? $this->username : $full;
        })->shouldCache();
    }
}
