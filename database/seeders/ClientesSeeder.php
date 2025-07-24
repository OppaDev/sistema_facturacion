<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password'); // Contraseña por defecto para seeders
        $clienteRole = Role::where('name', 'Cliente')->first();
        
        // Cliente 1: Juan Pérez
        $user1 = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juanperez@gmail.com',
            'password' => $password,
            'telefono' => '0999999999',
            'direccion' => 'Av. Principal 123',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);
        $user1->assignRole($clienteRole);
        
        // Cliente 2: María García
        $user2 = User::create([
            'name' => 'María García',
            'email' => 'mariagarcia@gmail.com',
            'password' => $password,
            'telefono' => '0988888888',
            'direccion' => 'Calle Secundaria 456',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);
        $user2->assignRole($clienteRole);
        
        // Cliente 3: Carlos López (inactivo)
        $user3 = User::create([
            'name' => 'Carlos López',
            'email' => 'carloslopez@gmail.com',
            'password' => $password,
            'telefono' => '0977777777',
            'direccion' => 'Av. Tercera 789',
            'estado' => 'inactivo',
            'email_verified_at' => now(),
        ]);
        $user3->assignRole($clienteRole);
        
        // Cliente 4: Ana Martínez (activo)
        $user4 = User::create([
            'name' => 'Ana Martínez',
            'email' => 'anamartinez@gmail.com',
            'password' => $password,
            'telefono' => '0966666666',
            'direccion' => 'Calle Cuarta 101',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);
        $user4->assignRole($clienteRole);
        
        // Cliente 5: Luis Rodríguez (activo)
        $user5 = User::create([
            'name' => 'Luis Rodríguez',
            'email' => 'luisrodriguez@gmail.com',
            'password' => $password,
            'telefono' => '0955555555',
            'direccion' => 'Av. Quinta 202',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);
        $user5->assignRole($clienteRole);
    }
}
