<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsuariosSeeder::class,
            CategoriasSeeder::class,
            ClientesSeeder::class, // Ahora crea usuarios con rol Cliente
            ProductosSeeder::class,
        ]);
    }
}
