<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

class ProductosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $vinos = Categoria::where('nombre', 'Vinos')->first();
        // $cervezas = Categoria::where('nombre', 'Cervezas')->first();
        // $licores = Categoria::where('nombre', 'Licores')->first();

        // Producto::create([
        //     'nombre' => 'Vino Tinto Reserva',
        //     'descripcion' => 'Vino tinto reserva Cabernet Sauvignon 750ml',
        //     'categoria_id' => $vinos->id,
        //     'stock' => 30,
        //     'precio' => 25.00,
        // ]);
        // Producto::create([
        //     'nombre' => 'Cerveza Artesanal IPA',
        //     'descripcion' => 'Cerveza artesanal IPA 355ml',
        //     'categoria_id' => $cervezas->id,
        //     'stock' => 60,
        //     'precio' => 4.50,
        // ]);
        // Producto::create([
        //     'nombre' => 'Whisky Escocés',
        //     'descripcion' => 'Whisky escocés single malt 12 años 750ml',
        //     'categoria_id' => $licores->id,
        //     'stock' => 15,
        //     'precio' => 85.00,
        // ]);
        // Producto::create([
        //     'nombre' => 'Ron Añejo',
        //     'descripcion' => 'Ron añejo premium 7 años 750ml',
        //     'categoria_id' => $licores->id,
        //     'stock' => 20,
        //     'precio' => 45.00,
        // ]);
        // Producto::create([
        //     'nombre' => 'Vodka Premium',
        //     'descripcion' => 'Vodka premium destilado 5 veces 750ml',
        //     'categoria_id' => $licores->id,
        //     'stock' => 25,
        //     'precio' => 35.00,
        // ]);
        // Producto::create([
        //     'nombre' => 'Cerveza Lager',
        //     'descripcion' => 'Cerveza lager premium pack de 6 botellas',
        //     'categoria_id' => $cervezas->id,
        //     'stock' => 40,
        //     'precio' => 12.00,
        // ]);
    }
}
