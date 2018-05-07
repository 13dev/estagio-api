<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fill a lot of users
        factory(User::class, 10)->create();

        User::truncate();
        $data = [];

        array_push($data, [
            'first_name' => 'Super',
            'last_name'  => 'Admin',
            'email'      => 'superadmin@admin.com',
            'password'   => app('hash')->make('secret'),
            'role'       => 'superadmin',
            'active'     => 1
        ]);

        array_push($data, [
            'first_name' => 'user',
            'last_name'  => 'User',
            'email'      => 'user@admin.com',
            'password'   => app('hash')->make('secret'),
            'role'       => 'user',
            'active'     => 1
        ]);

        User::insert($data);
    }
}
