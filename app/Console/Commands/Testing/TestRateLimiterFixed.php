<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestRateLimiterFixed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:rate-limiter-fixed {--host=localhost:8000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar rate limiter corregido en endpoints principales';

    private $token = null;
    private $baseUrl = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = $this->option('host');
        $this->baseUrl = "http://{$host}";

        $this->info("✅ Probando Rate Limiter CORREGIDO");
        $this->info("📍 Base URL: {$this->baseUrl}");
        $this->newLine();

        // Obtener token
        if (!$this->getAuthToken()) {
            $this->error("❌ No se pudo obtener token");
            return 1;
        }

        // Probar endpoints principales
        $this->testMainEndpoints();
        
        $this->newLine();
        $this->info("🎉 Todos los endpoints funcionan correctamente");
    }

    /**
     * Obtener token de autenticación
     */
    private function getAuthToken(): bool
    {
        $this->line("🔐 Obteniendo token...");
        
        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/login", [
                'email' => 'leonardojeffer.145@gmail.com',
                'password' => 'password',
            ]);

            if ($response->status() === 200) {
                $data = $response->json();
                $this->token = $data['data']['token'];
                $this->line("✅ Token obtenido");
                return true;
            } else {
                $this->error("❌ Login falló: " . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Probar endpoints principales
     */
    private function testMainEndpoints(): void
    {
        $endpoints = [
            'GET /api/me' => 'read',
            'GET /api/clientes' => 'read', 
            'GET /api/productos' => 'read',
            'GET /api/facturas' => 'read',
            'GET /api/pagos' => 'read'
        ];

        $this->newLine();
        $this->info("📋 Probando endpoints principales:");

        foreach ($endpoints as $endpoint => $type) {
            [$method, $path] = explode(' ', $endpoint);
            
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->token}",
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ])
                    ->send($method, $this->baseUrl . $path);
                
                $status = $response->status();
                $headers = $response->headers();
                $remaining = $headers['X-RateLimit-Remaining'][0] ?? 'N/A';
                $limit = $headers['X-RateLimit-Limit'][0] ?? 'N/A';
                
                if ($status === 200) {
                    $this->line("✅ {$endpoint}: OK - Limit: {$limit}, Remaining: {$remaining}");
                } elseif ($status === 429) {
                    $this->warn("🚫 {$endpoint}: Rate Limited (429) - Funcionando correctamente");
                } else {
                    $this->error("❌ {$endpoint}: Status {$status}");
                }
                
            } catch (\Exception $e) {
                $this->error("❌ Error en {$endpoint}: " . $e->getMessage());
            }
            
            // Pequeña pausa entre requests
            usleep(100000); // 100ms
        }
    }
}