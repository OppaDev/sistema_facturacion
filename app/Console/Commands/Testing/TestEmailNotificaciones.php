<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\Pago;
use App\Models\User;
use App\Models\Factura;
use App\Notifications\PagoRegistradoNotification;
use App\Notifications\PagoAprobadoNotification;
use App\Notifications\PagoRechazadoNotification;
use Illuminate\Support\Facades\Log;

class TestEmailNotificaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-notificaciones {--email=} {--pago_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testea el envío de notificaciones por email del sistema de pagos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando prueba de notificaciones por email...');
        
        // Obtener email de testing
        $emailTest = $this->option('email') ?? 'test@sowartech.com';
        
        // Obtener pago para testing
        $pagoId = $this->option('pago_id');
        
        if ($pagoId) {
            $pago = Pago::with(['factura.cliente'])->find($pagoId);
            if (!$pago) {
                $this->error("❌ No se encontró el pago con ID: {$pagoId}");
                return 1;
            }
        } else {
            // Buscar el primer pago disponible o crear uno de prueba
            $pago = Pago::with(['factura.cliente'])->first();
            if (!$pago) {
                $this->info('📋 Creando datos de prueba para las notificaciones...');
                
                // Crear usuario cliente de prueba
                $cliente = User::firstOrCreate([
                    'email' => $emailTest
                ], [
                    'name' => 'Cliente Test Notificaciones',
                    'password' => bcrypt('password123'),
                    'email_verified_at' => now()
                ]);
                
                // Crear factura de prueba
                $factura = Factura::create([
                    'cliente_id' => $cliente->id,
                    'subtotal' => 134.60,
                    'iva' => 16.15,
                    'total' => 150.75,
                    'estado' => 'pendiente'
                ]);
                
                // Crear pago de prueba
                $pago = Pago::create([
                    'factura_id' => $factura->id,
                    'tipo_pago' => 'transferencia',
                    'monto' => 150.75,
                    'numero_transaccion' => 'TEST-NOTIFICATIONS-' . time(),
                    'observacion' => 'Pago de prueba para testing completo de notificaciones',
                    'estado' => 'pendiente',
                    'pagado_por' => $cliente->id
                ]);
                
                $pago->load(['factura.cliente']);
                $this->info("✅ Datos de prueba creados: Cliente ID {$cliente->id}, Factura ID {$factura->id}, Pago ID {$pago->id}");
            }
        }

        $this->info("📋 Usando pago ID: {$pago->id} - Factura: {$pago->factura->getNumeroFormateado()}");
        
        // Crear usuario de prueba
        $usuarioTest = new User([
            'name' => 'Usuario Test',
            'email' => $emailTest
        ]);

        $this->info("📧 Enviando notificaciones a: {$emailTest}");
        
        // Test 1: Notificación de pago registrado
        $this->info("\n🔵 Test 1: Notificación de Pago Registrado");
        try {
            $usuarioTest->notify(new PagoRegistradoNotification($pago));
            $this->info("✅ Notificación de pago registrado enviada exitosamente");
            Log::info('Test email - Pago registrado enviado', ['email' => $emailTest, 'pago_id' => $pago->id]);
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar notificación de pago registrado: " . $e->getMessage());
            Log::error('Test email - Error pago registrado', ['error' => $e->getMessage()]);
        }

        sleep(2); // Pausa entre envíos

        // Test 2: Notificación de pago aprobado
        $this->info("\n🟢 Test 2: Notificación de Pago Aprobado");
        try {
            $usuarioTest->notify(new PagoAprobadoNotification($pago));
            $this->info("✅ Notificación de pago aprobado enviada exitosamente");
            Log::info('Test email - Pago aprobado enviado', ['email' => $emailTest, 'pago_id' => $pago->id]);
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar notificación de pago aprobado: " . $e->getMessage());
            Log::error('Test email - Error pago aprobado', ['error' => $e->getMessage()]);
        }

        sleep(2); // Pausa entre envíos

        // Test 3: Notificación de pago rechazado
        $this->info("\n🔴 Test 3: Notificación de Pago Rechazado");
        try {
            $motivoTest = "Datos de transferencia incorrectos. El número de transacción no coincide con nuestros registros.";
            $usuarioTest->notify(new PagoRechazadoNotification($pago, $motivoTest));
            $this->info("✅ Notificación de pago rechazado enviada exitosamente");
            Log::info('Test email - Pago rechazado enviado', ['email' => $emailTest, 'pago_id' => $pago->id]);
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar notificación de pago rechazado: " . $e->getMessage());
            Log::error('Test email - Error pago rechazado', ['error' => $e->getMessage()]);
        }

        // Mostrar información de configuración
        $this->info("\n📊 Información de configuración de email:");
        $this->info("🔧 Mailer por defecto: " . config('mail.default'));
        $this->info("🏠 Host SMTP: " . config('mail.mailers.maileroo.host'));
        $this->info("🚪 Puerto: " . config('mail.mailers.maileroo.port'));
        $this->info("🔐 Encriptación: " . config('mail.mailers.maileroo.encryption'));
        $this->info("📤 From address: " . config('mail.from.address'));
        $this->info("👤 From name: " . config('mail.from.name'));

        $this->info("\n✅ Prueba de notificaciones completada!");
        $this->info("🔍 Revisa los logs en storage/logs/laravel.log para más detalles");
        $this->info("📬 Revisa tu bandeja de entrada en: {$emailTest}");
        
        return 0;
    }
}