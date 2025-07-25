<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::create([
            'id' => Str::uuid(),
            'name' => 'Склад Москва',
        ]);

        Warehouse::create([
            'id' => Str::uuid(),
            'name' => 'Склад Европа',
        ]);
    }
}
