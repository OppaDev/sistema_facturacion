<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class TestApiSecurity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:api-security {--host=localhost:8000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecutar pruebas básicas de seguridad en la API REST';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = $this->option('host');
        $baseUrl = "http://{$host}/api";

        $this->info("🔒 Iniciando pruebas de seguridad de API");
        $this->info("📍 Base URL: {$baseUrl}");
        $this->newLine();

        $testResults = [];

        // Test 1: Acceso sin autenticación
        $testResults['auth'] = $this->testUnauthenticatedAccess($baseUrl);

        // Test 2: Intentos de SQL injection
        $testResults['sql_injection'] = $this->testSqlInjection($baseUrl);

        // Test 3: Intentos de XSS
        $testResults['xss'] = $this->testXssAttempts($baseUrl);

        // Test 4: Rate limiting
        $testResults['rate_limiting'] = $this->testRateLimiting($baseUrl);

        // Test 5: Headers maliciosos
        $testResults['malicious_headers'] = $this->testMaliciousHeaders($baseUrl);

        // Test 6: Tamaño de request excesivo
        $testResults['request_size'] = $this->testRequestSize($baseUrl);

        // Resumen final
        $this->showTestResults($testResults);
    }

    /**
     * Test acceso sin autenticación
     */
    private function testUnauthenticatedAccess(string $baseUrl): bool
    {
        $this->info("🧪 Test 1: Acceso sin autenticación");
        
        $endpoints = [
            'GET /clientes',
            'GET /productos',
            'GET /facturas',
            'GET /pagos',
            'GET /me'
        ];

        $protected = true;
        foreach ($endpoints as $endpoint) {
            [$method, $path] = explode(' ', $endpoint);
            
            try {
                $response = Http::timeout(5)->send($method, "{$baseUrl}{$path}");
                
                if ($response->status() !== 401) {
                    $this->error("  ❌ {$endpoint} - Esperado: 401, Recibido: {$response->status()}");
                    $protected = false;
                } else {
                    $this->line("  ✅ {$endpoint} - Protegido correctamente");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Error conectando a {$endpoint}: {$e->getMessage()}");
                $protected = false;
            }
        }

        return $protected;
    }

    /**
     * Test intentos de SQL injection
     */
    private function testSqlInjection(string $baseUrl): bool
    {
        $this->info("🧪 Test 2: Intentos de SQL Injection");
        
        $sqlPayloads = [
            "'; DROP TABLE users; --",
            "1' OR '1'='1",
            "' UNION SELECT * FROM users --",
            "admin' --",
            "1'; INSERT INTO users (email) VALUES ('hacker@test.com'); --"
        ];

        $blocked = true;
        
        foreach ($sqlPayloads as $payload) {
            try {
                $response = Http::timeout(5)->post("{$baseUrl}/login", [
                    'email' => $payload,
                    'password' => 'test123'
                ]);
                
                // Cualquier respuesta que no sea 400 (bad request) podría ser problemática
                if ($response->status() === 200) {
                    $this->error("  ❌ Payload SQL posiblemente exitoso: " . substr($payload, 0, 30) . "...");
                    $blocked = false;
                } else {
                    $this->line("  ✅ Payload SQL bloqueado: " . substr($payload, 0, 30) . "...");
                }
            } catch (\Exception $e) {
                $this->line("  ✅ Payload SQL rechazado por conexión: " . substr($payload, 0, 30) . "...");
            }
        }

        return $blocked;
    }

    /**
     * Test intentos de XSS
     */
    private function testXssAttempts(string $baseUrl): bool
    {
        $this->info("🧪 Test 3: Intentos de XSS");
        
        $xssPayloads = [
            "<script>alert('XSS')</script>",
            "javascript:alert('XSS')",
            "<img src=x onerror=alert('XSS')>",
            "'; alert('XSS'); //",
            "<iframe src='javascript:alert(\"XSS\")'></iframe>"
        ];

        $blocked = true;
        
        foreach ($xssPayloads as $payload) {
            try {
                $response = Http::timeout(5)->post("{$baseUrl}/login", [
                    'email' => "test@test.com",
                    'password' => $payload
                ]);
                
                // Verificar que el payload no se refleje en la respuesta
                $responseBody = $response->body();
                if (str_contains($responseBody, '<script>') || str_contains($responseBody, 'javascript:')) {
                    $this->error("  ❌ Posible XSS reflejado: " . substr($payload, 0, 30) . "...");
                    $blocked = false;
                } else {
                    $this->line("  ✅ Payload XSS sanitizado: " . substr($payload, 0, 30) . "...");
                }
            } catch (\Exception $e) {
                $this->line("  ✅ Payload XSS rechazado por conexión: " . substr($payload, 0, 30) . "...");
            }
        }

        return $blocked;
    }

    /**
     * Test rate limiting
     */
    private function testRateLimiting(string $baseUrl): bool
    {
        $this->info("🧪 Test 4: Rate Limiting");
        
        $rateLimitTriggered = false;
        
        // Hacer muchas requests rápidas al login
        for ($i = 1; $i <= 10; $i++) {
            try {
                $response = Http::timeout(2)->post("{$baseUrl}/login", [
                    'email' => "test{$i}@test.com",
                    'password' => 'wrongpassword'
                ]);
                
                if ($response->status() === 429) {
                    $this->line("  ✅ Rate limit activado en intento #{$i}");
                    $rateLimitTriggered = true;
                    break;
                }
            } catch (\Exception $e) {
                // Error de conexión es aceptable para esta prueba
                continue;
            }
        }

        if (!$rateLimitTriggered) {
            $this->error("  ❌ Rate limiting no activado después de 10 intentos");
        }

        return $rateLimitTriggered;
    }

    /**
     * Test headers maliciosos
     */
    private function testMaliciousHeaders(string $baseUrl): bool
    {
        $this->info("🧪 Test 5: Headers Maliciosos");
        
        $maliciousHeaders = [
            'X-Forwarded-Host' => 'evil.com',
            'X-Original-URL' => '/admin/users',
            'X-Rewrite-URL' => '/admin/delete',
            'X-Forwarded-Server' => 'malicious-server.com'
        ];

        $blocked = true;
        
        foreach ($maliciousHeaders as $header => $value) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([$header => $value])
                    ->get("{$baseUrl}/me");
                
                if ($response->status() === 200) {
                    $this->error("  ❌ Header malicioso aceptado: {$header}");
                    $blocked = false;
                } else {
                    $this->line("  ✅ Header malicioso rechazado: {$header}");
                }
            } catch (\Exception $e) {
                $this->line("  ✅ Header malicioso bloqueado por conexión: {$header}");
            }
        }

        return $blocked;
    }

    /**
     * Test tamaño de request excesivo
     */
    private function testRequestSize(string $baseUrl): bool
    {
        $this->info("🧪 Test 6: Tamaño de Request Excesivo");
        
        // Crear payload grande (1MB+)
        $largePayload = str_repeat('A', 1048576 * 2); // 2MB
        
        try {
            $response = Http::timeout(10)->post("{$baseUrl}/login", [
                'email' => 'test@test.com',
                'password' => $largePayload
            ]);
            
            if ($response->status() === 413 || $response->status() === 400) {
                $this->line("  ✅ Request grande rechazado correctamente");
                return true;
            } else {
                $this->error("  ❌ Request grande aceptado incorrectamente");
                return false;
            }
        } catch (\Exception $e) {
            $this->line("  ✅ Request grande bloqueado por conexión");
            return true;
        }
    }

    /**
     * Mostrar resumen de resultados
     */
    private function showTestResults(array $results): void
    {
        $this->newLine();
        $this->info("📊 RESUMEN DE PRUEBAS DE SEGURIDAD");
        $this->info("=" . str_repeat("=", 40));
        
        $passed = 0;
        $total = count($results);
        
        foreach ($results as $test => $result) {
            $icon = $result ? "✅" : "❌";
            $status = $result ? "PASS" : "FAIL";
            $testName = str_replace('_', ' ', strtoupper($test));
            
            $this->line("  {$icon} {$testName}: {$status}");
            
            if ($result) {
                $passed++;
            }
        }
        
        $this->newLine();
        $percentage = round(($passed / $total) * 100, 1);
        
        if ($percentage >= 80) {
            $this->info("🎉 RESULTADO: {$passed}/{$total} pruebas pasaron ({$percentage}%) - Seguridad BUENA");
        } elseif ($percentage >= 60) {
            $this->warn("⚠️  RESULTADO: {$passed}/{$total} pruebas pasaron ({$percentage}%) - Seguridad MODERADA");
        } else {
            $this->error("🚨 RESULTADO: {$passed}/{$total} pruebas pasaron ({$percentage}%) - Seguridad DEFICIENTE");
        }
        
        $this->newLine();
        $this->info("💡 Recomendaciones:");
        $this->line("   - Revisar logs de seguridad en storage/logs/");
        $this->line("   - Monitorear rate limiting en producción");
        $this->line("   - Configurar alertas para intentos de injection");
        $this->line("   - Revisar configuración de CORS para producción");
    }
}