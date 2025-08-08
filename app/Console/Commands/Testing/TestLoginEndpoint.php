<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestLoginEndpoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:login {--host=localhost:8000} {--email=leonardojeffer.145@gmail.com} {--password=password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el endpoint de login de la API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = $this->option('host');
        $email = $this->option('email');
        $password = $this->option('password');
        $baseUrl = "http://{$host}";

        $this->info("🧪 Probando endpoint de login");
        $this->info("📍 URL: {$baseUrl}/api/login");
        $this->info("📧 Email: {$email}");
        $this->newLine();

        try {
            $response = Http::timeout(10)->post("{$baseUrl}/api/login", [
                'email' => $email,
                'password' => $password,
            ]);

            $statusCode = $response->status();
            $body = $response->json();

            $this->info("📊 RESULTADO:");
            $this->line("Status Code: {$statusCode}");
            
            if ($statusCode === 200) {
                $this->line("✅ Login exitoso");
                $this->line("👤 Usuario: " . $body['data']['user']['name']);
                $this->line("🔑 Token generado: " . substr($body['data']['token'], 0, 20) . "...");
                $this->line("👥 Roles: " . implode(', ', $body['data']['user']['roles']));
            } else {
                $this->error("❌ Login falló");
                if (isset($body['message'])) {
                    $this->error("Mensaje: " . $body['message']);
                }
                if (isset($body['errors'])) {
                    $this->error("Errores: " . json_encode($body['errors']));
                }
            }
            
            $this->newLine();
            $this->line("📄 Respuesta completa:");
            $this->line(json_encode($body, JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            $this->error("❌ Error al conectar al servidor:");
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn("💡 Asegúrate de que el servidor esté ejecutándose:");
            $this->line("   php artisan serve --host=localhost --port=8000");
        }
    }
}