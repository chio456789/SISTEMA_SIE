<?php

namespace Database\Seeders;


use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        Sucursal::create([
            'name' => 'SIE',
            'adress' => 'Av América casi esquina G Rene Moreno',
            'telefono' => 4240013,
            'celular' => 79771777,
            'nit_id' => '75010065981',
            'company_id' => '1'
        ]);
    }
    //'name','adress','telefono','celular','nit_id','company_id'
}