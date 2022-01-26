<?php

use Illuminate\Database\Seeder;

class WatermarkTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Watermark::class, 1)->states('center_logo')->create();
        factory(App\Watermark::class, 1)->states('fill_logo')->create();
    }
}
