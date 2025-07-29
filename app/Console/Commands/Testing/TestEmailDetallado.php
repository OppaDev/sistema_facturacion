<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\Factura;
use Illuminate\Support\Facades\Log;

class TestEmailDetallado extends Command
{
    protected $signature = 'test:email-detallado {email}';
    protected $description = 'Probar envio de email con diagnostico detallado';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🔍 Iniciando diagnostico de email...");
        $this->info("📧 Email destino: {$email}");
        
        // 1. Verificar configuracion
        $this->info("\n📋 Verificando configuracion...");
        $emailService = new EmailService();
        $config = $emailService->verificarConfiguracion();
        
        foreach ($config as $key => $value) {
            $this->info("  {$key}: {$value}");
        }
        
        // 2. Verificar si hay facturas emitidas
        $this->info("\n📄 Buscando facturas emitidas...");
        $factura = Factura::where('estado', 'EMITIDA')->first();
        
        if (!$factura) {
            $this->error("❌ No hay facturas emitidas. Creando una de prueba...");
            
            // Crear factura de prueba
            $factura = new Factura();
            $factura->numero_factura = '001-001-000000001';
            $factura->cliente_id = 1;
            $factura->subtotal = 100.00;
            $factura->iva = 12.00;
            $factura->total = 112.00;
            $factura->estado = 'activa';
            $factura->usuario_id = 1;
            $factura->save();
            
            $this->info("✅ Factura de prueba creada con ID: {$factura->id}");
        } else {
            $this->info("✅ Factura encontrada: #{$factura->numero_factura}");
        }
        
        // 3. Verificar librerías
        $this->info("\n📚 Verificando librerías...");
        
        if (class_exists('\SendGrid\SendGrid')) {
            $this->info("✅ SendGrid librería disponible");
        } else {
            $this->error("❌ SendGrid librería no disponible");
        }
        
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $this->info("✅ DomPDF librería disponible");
        } else {
            $this->error("❌ DomPDF librería no disponible");
        }
        
        // 4. Verificar vistas
        $this->info("\n👁️ Verificando vistas...");
        
        try {
            view('emails.factura');
            $this->info("✅ Vista emails.factura disponible");
        } catch (\Exception $e) {
            $this->error("❌ Vista emails.factura no encontrada: " . $e->getMessage());
        }
        
        try {
            view('facturas.pdf');
            $this->info("✅ Vista facturas.pdf disponible");
        } catch (\Exception $e) {
            $this->error("❌ Vista facturas.pdf no encontrada: " . $e->getMessage());
        }
        
        // 5. Probar generación de PDF
        $this->info("\n📄 Probando generación de PDF...");
        try {
            $html = view('facturas.pdf', compact('factura'))->render();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdfContent = $pdf->output();
            $this->info("✅ PDF generado correctamente (" . strlen($pdfContent) . " bytes)");
        } catch (\Exception $e) {
            $this->error("❌ Error generando PDF: " . $e->getMessage());
        }
        
        // 6. Probar generación de HTML
        $this->info("\n📧 Probando generación de HTML...");
        try {
            $mensaje = "Esta es una prueba de envio de factura.";
            $html = view('emails.factura', compact('factura', 'mensaje'))->render();
            $this->info("✅ HTML generado correctamente (" . strlen($html) . " bytes)");
        } catch (\Exception $e) {
            $this->error("❌ Error generando HTML: " . $e->getMessage());
        }
        
        // 7. Probar envio real
        $this->info("\n🚀 Probando envio real...");
        try {
            $resultado = $emailService->enviarFactura(
                $factura,
                $email,
                "Prueba de Factura - " . now()->format('d/m/Y H:i:s'),
                "Esta es una prueba de envio de factura desde el sistema."
            );
            
            if ($resultado) {
                $this->info("✅ Email enviado exitosamente");
                $this->info("📧 Revisa tu bandeja de entrada en: {$email}");
                $this->info("📧 También revisa la carpeta de spam");
            } else {
                $this->error("❌ Error al enviar email");
            }
        } catch (\Exception $e) {
            $this->error("❌ Excepción al enviar email: " . $e->getMessage());
            $this->error("📋 Stack trace: " . $e->getTraceAsString());
        }
        
        // 8. Verificar logs
        $this->info("\n📋 Últimos logs de email:");
        try {
            // Leer los últimos logs del archivo de logs
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $logs = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $recentLogs = array_slice($logs, -5);
                foreach ($recentLogs as $log) {
                    $this->line("  " . $log);
                }
            } else {
                $this->info("  No hay archivo de logs disponible");
            }
        } catch (\Exception $e) {
            $this->info("  Error leyendo logs: " . $e->getMessage());
        }
        
        $this->info("\n🎯 Diagnostico completado");
        $this->info("💡 Si no recibes el email, verifica:");
        $this->info("  1. Tu carpeta de spam");
        $this->info("  2. Que el email esté correcto");
        $this->info("  3. Los logs de Laravel en storage/logs/laravel.log");
        
        return 0;
    }
} 