<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Factura;
use App\Models\Pago;
use App\Notifications\PagoRegistradoNotification;

class TestEmailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-config {--email=test@sowartech.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testea la configuración de email Maileroo y notificaciones de pago';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emailTest = $this->option('email');
        
        $this->info("🔧 Testeando configuración de email Maileroo para: {$emailTest}");
        
        // Verificar configuración
        $this->info('📊 Configuración actual:');
        $this->info("Mailer: " . config('mail.default'));
        $this->info("Host: " . config('mail.mailers.maileroo.host', 'No configurado'));
        $this->info("Puerto: " . config('mail.mailers.maileroo.port', 'No configurado'));
        $this->info("From: " . config('mail.from.address', 'No configurado'));
        
        try {
            // Verificar si tenemos datos de prueba
            $this->info("\n📋 Creando datos de prueba...");
            
            // Buscar o crear usuario cliente de prueba
            $cliente = User::firstOrCreate([
                'email' => $emailTest
            ], [
                'name' => 'Cliente Test Notificaciones',
                'password' => bcrypt('password123'),
                'email_verified_at' => now()
            ]);
            
            $this->info("✅ Cliente de prueba: {$cliente->name} ({$cliente->email})");
            
            // Crear factura de prueba
            $factura = Factura::create([
                'cliente_id' => $cliente->id,
                'subtotal' => 134.60,
                'iva' => 16.15,
                'total' => 150.75,
                'estado' => 'pendiente'
            ]);
            
            $this->info("✅ Factura de prueba creada: ID {$factura->id}");
            
            // Crear pago de prueba
            $pago = Pago::create([
                'factura_id' => $factura->id,
                'tipo_pago' => 'transferencia',
                'monto' => 150.75,
                'numero_transaccion' => 'TEST-MAILEROO-' . time(),
                'observacion' => 'Pago de prueba para testing de notificaciones con Maileroo',
                'estado' => 'pendiente',
                'pagado_por' => $cliente->id
            ]);
            
            $this->info("✅ Pago de prueba creado: ID {$pago->id}");
            
            // Test 1: Email básico con Maileroo
            $this->info("\n📧 Test 1: Email básico con Maileroo...");
            
            Mail::raw('🔧 Test de configuración Maileroo - Sistema de Facturación. Este email confirma que Maileroo está funcionando correctamente.', function ($message) use ($emailTest) {
                $message->to($emailTest)
                        ->subject('🔧 Test Maileroo - Sistema de Facturación');
            });
            $this->info("✅ Email básico enviado exitosamente");
            
            // Test 2: Notificación de pago registrado
            $this->info("\n🔔 Test 2: Notificación de pago registrado...");
            
            $cliente->notify(new PagoRegistradoNotification($pago));
            $this->info("✅ Notificación de pago registrado enviada");
            Log::info('Test Maileroo - Notificación enviada', ['pago_id' => $pago->id]);
            
            // Resumen
            $this->info("\n🎯 Resumen del test:");
            $this->info("📧 Email de destino: {$emailTest}");
            $this->info("👤 Cliente ID: {$cliente->id}");
            $this->info("📋 Factura ID: {$factura->id}");
            $this->info("💰 Pago ID: {$pago->id}");
            $this->info("🏠 Host SMTP: " . config('mail.mailers.maileroo.host'));
            
            $this->info("\n✅ Test completado exitosamente!");
            $this->info("📧 Revisa tu email en: {$emailTest}");
            $this->info("📧 También revisa la carpeta de spam/promociones");
            $this->info("🔍 Logs disponibles en: storage/logs/laravel.log");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error durante el test: " . $e->getMessage());
            Log::error('Test Maileroo - Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            $this->info("\n💡 Soluciones posibles:");
            $this->info("1. Verifica las credenciales de Maileroo en .env");
            $this->info("2. Verifica que MAIL_HOST=smtp.maileroo.com");
            $this->info("3. Verifica que MAIL_PORT=587 y MAIL_ENCRYPTION=tls");
            $this->info("4. Revisa los logs de Laravel para más detalles");
            
            return 1;
        }
    }
} 