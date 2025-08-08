<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\Pago;
use App\Models\User;
use App\Notifications\PagoAprobadoNotification;
use App\Notifications\PagoRechazadoNotification;
use Illuminate\Support\Facades\Log;

class TestNotificacionesPagos extends Command
{
    protected $signature = 'test:notificaciones-pagos {--pago_id=} {--email=test@sowartech.com}';
    protected $description = 'Testea las notificaciones de aprobación y rechazo de pagos';

    public function handle()
    {
        $this->info('🧪 Iniciando test de notificaciones de pagos...');
        
        $pagoId = $this->option('pago_id');
        $emailTest = $this->option('email');
        
        // Obtener pago para testing
        if ($pagoId) {
            $pago = Pago::with(['factura.cliente'])->find($pagoId);
            if (!$pago) {
                $this->error("❌ No se encontró el pago con ID: {$pagoId}");
                return 1;
            }
        } else {
            $pago = Pago::with(['factura.cliente'])->where('estado', 'pendiente')->first();
            if (!$pago) {
                $this->error('❌ No hay pagos pendientes en el sistema para probar');
                return 1;
            }
        }

        $this->info("📋 Usando pago ID: {$pago->id}");
        $this->info("💰 Monto: $" . number_format($pago->monto, 2));
        $this->info("📧 Cliente: {$pago->factura->cliente->name}");
        
        // Crear usuario de prueba para recibir emails
        $usuarioTest = new User([
            'name' => $pago->factura->cliente->name ?? 'Cliente Test',
            'email' => $emailTest
        ]);

        $this->info("📤 Enviando emails a: {$emailTest}");
        
        // Test 1: Notificación de pago aprobado
        $this->info("\n🟢 Test 1: Notificación de Pago Aprobado");
        try {
            $usuarioTest->notify(new PagoAprobadoNotification($pago));
            $this->info("✅ Notificación de pago aprobado enviada exitosamente");
            Log::info('Test notificaciones - Pago aprobado enviado', [
                'email' => $emailTest, 
                'pago_id' => $pago->id
            ]);
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar notificación de pago aprobado: " . $e->getMessage());
            Log::error('Test notificaciones - Error pago aprobado', [
                'error' => $e->getMessage(),
                'pago_id' => $pago->id
            ]);
        }

        sleep(2); // Pausa entre envíos

        // Test 2: Notificación de pago rechazado
        $this->info("\n🔴 Test 2: Notificación de Pago Rechazado");
        try {
            $motivoTest = "Los datos de la transferencia no coinciden con nuestros registros. Por favor, verifica el número de transacción y vuelve a intentarlo.";
            $usuarioTest->notify(new PagoRechazadoNotification($pago, $motivoTest));
            $this->info("✅ Notificación de pago rechazado enviada exitosamente");
            Log::info('Test notificaciones - Pago rechazado enviado', [
                'email' => $emailTest, 
                'pago_id' => $pago->id,
                'motivo' => $motivoTest
            ]);
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar notificación de pago rechazado: " . $e->getMessage());
            Log::error('Test notificaciones - Error pago rechazado', [
                'error' => $e->getMessage(),
                'pago_id' => $pago->id
            ]);
        }

        // Verificar configuración de email
        $this->info("\n📊 Información de configuración:");
        $this->info("🔧 Mailer: " . config('mail.default'));
        $this->info("🏠 Host: " . config('mail.mailers.maileroo.host', 'No configurado'));
        $this->info("🚪 Puerto: " . config('mail.mailers.maileroo.port', 'No configurado'));
        $this->info("📤 From: " . config('mail.from.address', 'No configurado'));

        // Verificar si las queues están funcionando
        $this->info("\n🔄 Información de Queues:");
        $this->info("📋 Queue Driver: " . config('queue.default'));
        
        if (config('queue.default') !== 'sync') {
            $this->warn("⚠️ Las notificaciones se están procesando en background");
            $this->info("💡 Ejecuta: php artisan queue:work para procesar los emails");
        } else {
            $this->info("✅ Las notificaciones se procesan inmediatamente (sync)");
        }

        $this->info("\n✅ Test de notificaciones completado!");
        $this->info("📧 Revisa tu bandeja de entrada: {$emailTest}");
        $this->info("🔍 Revisa los logs en storage/logs/laravel.log");
        
        return 0;
    }
}