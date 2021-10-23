<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Tom',
            'email' => 'tom@com.tw',
            'password' => Hash::make('123456')
        ]);

        DB::table('users')->insert([
            'name' => 'hi',
            'email' => 'hi@com.tw',
            'password' => Hash::make('123456')
        ]);
    }
}
