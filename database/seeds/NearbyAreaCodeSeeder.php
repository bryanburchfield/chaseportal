<?php

use App\Models\NearbyAreaCode;
use Illuminate\Database\Seeder;

class NearbyAreaCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array_map('str_getcsv', file('database/data/nearby_area_codes.csv'));

        $keys = ['source_npa', 'nearby_npa'];

        $insert_data = [];

        foreach ($data as $rec) {
            $insert_data[] = array_combine($keys, $rec);
        }

        NearbyAreaCode::insert($insert_data);
    }
}
