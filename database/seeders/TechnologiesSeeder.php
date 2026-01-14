<?php

namespace Database\Seeders;

use App\Models\Technology;
use Illuminate\Database\Seeder;

class TechnologiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $technologies = [
            ['name' => 'Web'],
            ['name' => 'Mobile'],
            ['name' => 'DevOps'],
            ['name' => 'AI'],
        ];

        foreach ($technologies as $technology) {
            Technology::query()->create($technology);
        }
    }
}
