<?php

namespace Database\Seeders;

use App\Models\SingleNumber;
use Illuminate\Database\Seeder;

class SingleNumberSeeder extends Seeder
{
    public function run()
    {
        SingleNumber::insert([
            ['single_number' => 1, 'single_order' => 1],
            ['single_number' => 2, 'single_order' => 2],
            ['single_number' => 3, 'single_order' => 3],
            ['single_number' => 4, 'single_order' => 4],
            ['single_number' => 5, 'single_order' => 5],
            ['single_number' => 6, 'single_order' => 6],
            ['single_number' => 7, 'single_order' => 7],
            ['single_number' => 8, 'single_order' => 8],
            ['single_number' => 9, 'single_order' => 9],
            ['single_number' => 0, 'single_order' => 10]
        ]);
    }
}
