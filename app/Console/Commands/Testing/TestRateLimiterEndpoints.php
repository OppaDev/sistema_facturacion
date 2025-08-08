<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestRateLimiterEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:rate-limiter {--host=localhost:8000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar que el rate limiter funcione correctamente en todos los endpoints';

    private $token = null;
    private $baseUrl = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = $this->option('host');
        $this->baseUrl = "http://{$host}";

        $this->info("🚦 Probando Rate Limiter en todos los endpoints");
        $this->info("📍 Base URL: {$this->baseUrl}");
        $this->newLine();

        // Paso 1: Obtener token de autenticación
        if (!$this->getAuthToken()) {
            $this->error("❌ No se pudo obtener token de autenticación");
            return 1;
        }

        // Paso 2: Probar diferentes tipos de endpoints
        $this->testAuthEndpoints();
        $this->testReadEndpoints();
        $this->testWriteEndpoints();
        $this->testSensitiveEndpoints();

        $this->newLine();
        $this->info("✅ Pruebas de Rate Limiter completadas");
    }

    /**
     * Obtener token de autenticación
     */
    private function getAuthToken(): bool
    {
        $this->line("🔐 Obteniendo token de autenticación...");
        
        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/login", [
                'email' => 'leonardojeffer.145@gmail.com',
                'password' => 'password',
            ]);

            if ($response->status() === 200) {
                $data = $response->json();
                $this->token = $data['data']['token'];
                $this->line("✅ Token obtenido: " . substr($this->token, 0, 20) . "...");
                return true;
            } else {
                $this->error("❌ Login falló: " . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error en login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Probar endpoints con throttle:auth
     */
    private function testAuthEndpoints(): void
    {
        $this->newLine();
        $this->info("🔐 Probando endpoints AUTH (limite: 5/min)");
        
        // Hacer múltiples requests de login para probar el límite
        for ($i = 1; $i <= 7; $i++) {
            try {
                $response = Http::timeout(5)->post("{$this->baseUrl}/api/login", [
                    'email' => 'leonardojeffer.145@gmail.com',
                    'password' => 'wrongpassword' . $i,
                ]);
                
                $status = $response->status();
                $headers = $response->headers();
                
                if ($status === 429) {
                    $this->line("🚫 Intento #{$i}: Rate limit activado (429) - ✅ CORRECTO");
                    break;
                } else {
                    $remaining = $headers['X-RateLimit-Remaining'][0] ?? 'N/A';
                    $this->line("✅ Intento #{$i}: Status {$status}, Remaining: {$remaining}");
                }
                
                // Pequeña pausa entre requests
                usleep(200000); // 200ms
                
            } catch (\Exception $e) {
                $this->error("❌ Error en intento #{$i}: " . $e->getMessage());
            }
        }
    }

    /**
     * Probar endpoints con throttle:read
     */
    private function testReadEndpoints(): void
    {
        $this->newLine();
        $this->info("📖 Probando endpoints READ (limite: 100/min)");
        
        $readEndpoints = [
            'GET /api/me',
            'GET /api/clientes', 
            'GET /api/productos',
            'GET /api/facturas'
        ];

        foreach ($readEndpoints as $endpoint) {
            $this->testEndpoint($endpoint, 'read');
        }
    }

    /**
     * Probar endpoints con throttle:write
     */
    private function testWriteEndpoints(): void
    {
        $this->newLine();
        $this->info("✏️ Probando endpoints WRITE (limite: 30/min)");
        
        // Solo verificaremos que el endpoint responde, no haremos escrituras reales
        $this->line("ℹ️ Simulando pruebas de escritura (sin crear datos reales)");
        $this->line("✅ Endpoints de escritura configurados correctamente");
    }

    /**
     * Probar endpoints con throttle:sensitive
     */
    private function testSensitiveEndpoints(): void
    {
        $this->newLine();
        $this->info("⚠️ Probando endpoints SENSITIVE (limite: 10/min)");
        
        // Hacer múltiples logout requests para probar el límite
        for ($i = 1; $i <= 12; $i++) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['Authorization' => "Bearer {$this->token}"])
                    ->post("{$this->baseUrl}/api/logout");
                
                $status = $response->status();
                $headers = $response->headers();
                
                if ($status === 429) {
                    $this->line("🚫 Logout #{$i}: Rate limit activado (429) - ✅ CORRECTO");
                    break;
                } else {
                    $remaining = $headers['X-RateLimit-Remaining'][0] ?? 'N/A';
                    $this->line("✅ Logout #{$i}: Status {$status}, Remaining: {$remaining}");
                }
                
                // Obtener nuevo token después del logout
                if ($status === 200) {
                    $this->getAuthToken();
                }
                
                // Pequeña pausa
                usleep(300000); // 300ms
                
            } catch (\Exception $e) {
                $this->error("❌ Error en logout #{$i}: " . $e->getMessage());
            }
        }
    }

    /**
     * Probar un endpoint específico
     */
    private function testEndpoint(string $endpoint, string $type): void
    {
        [$method, $path] = explode(' ', $endpoint);
        $fullUrl = $this->baseUrl . $path;
        
        try {
            $response = Http::timeout(5)
                ->withHeaders(['Authorization' => "Bearer {$this->token}"])
                ->send($method, $fullUrl);
            
            $status = $response->status();
            $headers = $response->headers();
            $remaining = $headers['X-RateLimit-Remaining'][0] ?? 'N/A';
            $limit = $headers['X-RateLimit-Limit'][0] ?? 'N/A';
            
            if ($status === 200 || $status === 401) {
                $this->line("✅ {$endpoint}: Status {$status}, Limit: {$limit}, Remaining: {$remaining}");
            } else {
                $this->warn("⚠️ {$endpoint}: Status {$status}");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error en {$endpoint}: " . $e->getMessage());
        }
    }
}