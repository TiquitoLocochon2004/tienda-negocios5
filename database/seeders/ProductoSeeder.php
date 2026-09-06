<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        Categoria::all()->each(function (Categoria $categoria): void {
            Producto::factory()->count(3)->create([
                'categoria_id' => $categoria->id,
            ]);
        });
    }
}
