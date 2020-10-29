<?php

use App\Models\AreaCode;
use Illuminate\Database\Seeder;

class AreaCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array_map('str_getcsv', file('database/data/area_codes.csv'));

        $keys = ['npa', 'city', 'state', 'timezone'];

        $insert_data = [];

        foreach ($data as $rec) {
            $insert_data[] = array_combine($keys, $rec);
        }

        AreaCode::insert($insert_data);
    }
}
