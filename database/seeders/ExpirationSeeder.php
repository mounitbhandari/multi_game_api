<?php

namespace Database\Seeders;

use App\Models\Expiration;
use Illuminate\Database\Seeder;

class ExpirationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Expiration::create(['days' => 0]);
    }
}
