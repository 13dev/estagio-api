<?php

use App\Models\Destiny;
use Illuminate\Database\Seeder;

class DestinyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Destiny::class, 15)->create();
    }
}
