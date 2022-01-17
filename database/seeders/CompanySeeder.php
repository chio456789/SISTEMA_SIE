<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        Company::create([
            'name' => 'SIE',
            'adress' => 'Av América casi esquina G Rene Moreno',
            'phone' => 4240013,
            'nit_id' => '75010065981'
        ]);
    }
    //'name','adress','phone','nit_id'
}
