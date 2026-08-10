<?php

namespace Database\Seeders;

use App\Models\Firm;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Seeding runs outside a request, so there is no authenticated user to
     * derive a firm from. Firm-owned records must therefore be created inside
     * actAs(), or the global scope will hide them from you afterwards.
     */
    public function run(): void
    {
        $tenancy = app(Tenancy::class);

        $firms = [
            ['name' => 'Tan & Associates', 'slug' => 'tan-associates'],
            ['name' => 'Rahman Legal', 'slug' => 'rahman-legal'],
        ];

        foreach ($firms as $index => $attributes) {
            $firm = Firm::create($attributes);

            $tenancy->actAs($firm->id, function () use ($firm, $index) {
                User::factory()->create([
                    'firm_id' => $firm->id,
                    'username' => 'admin'.($index + 1),
                    'email' => "admin@{$firm->slug}.test",
                ]);
            });
        }
    }
}
