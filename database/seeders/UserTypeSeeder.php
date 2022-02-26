<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserType::create(['user_type_name' => 'Admin']);
        UserType::create(['user_type_name' => 'Developer']);
        UserType::create(['user_type_name' => 'Super Stockist']);
        UserType::create(['user_type_name' => 'Stockist']);
        UserType::create(['user_type_name' => 'Terminal']);
//        $this->command->info('User Type creation Finished');
    }
}
