<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?User $user = null): array
    {
        return [
            'username' => $this->usernameRules($user),
            'email' => $this->emailRules($user?->id),
        ];
    }

    /**
     * Get the validation rules used to validate usernames.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function usernameRules(?User $user = null): array
    {
        $rules = ['required', 'string', 'max:255'];

        // Usernames are unique per firm, not globally — lfp_users carries a
        // composite unique on (firm_id, username). With no user there is no
        // firm to scope the check to: registration creates a brand new firm,
        // whose first username cannot collide with anything.
        if ($user !== null) {
            $rules[] = Rule::unique(User::class, 'username')
                ->where('firm_id', $user->firm_id)
                ->ignore($user->id);
        }

        return $rules;
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
