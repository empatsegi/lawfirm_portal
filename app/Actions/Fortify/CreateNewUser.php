<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Firm;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Roles are plain integers on lfp_users with no enum backing them yet; 1 is
     * what the factory and seeder both treat as the firm administrator.
     */
    private const ROLE_FIRM_ADMIN = 1;

    /**
     * Registration is self-serve firm signup: there is no firm to join yet, so
     * the registrant creates one and becomes its first user. Adding further
     * users to an existing firm is an invite flow, not this.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'firm_name' => ['required', 'string', 'max:255'],
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $firm = Firm::create([
                'name' => $input['firm_name'],
                'slug' => $this->uniqueSlug($input['firm_name']),
            ]);

            // firm_id is not fillable — BelongsToFirm stamps it from the
            // ambient tenancy on create, and there is no authenticated user to
            // derive that from mid-registration.
            return app(Tenancy::class)->actAs($firm->id, fn (): User => User::create([
                'username' => $input['username'],
                'role' => self::ROLE_FIRM_ADMIN,
                'email' => $input['email'],
                'password' => $input['password'],
            ]));
        });
    }

    /**
     * lfp_firms.slug is globally unique, and firm names are not. Suffix until
     * free rather than failing a signup on someone else's choice of name.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'firm';
        $slug = $base;
        $suffix = 1;

        while (Firm::where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
