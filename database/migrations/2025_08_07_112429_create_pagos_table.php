<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            
            // Referencia a la factura
            $table->foreignId('factura_id')->constrained('facturas')->onDelete('cascade');
            
            // Tipo de pago
            $table->enum('tipo_pago', ['efectivo', 'tarjeta', 'transferencia', 'cheque']);
            
            // Monto pagado
            $table->decimal('monto', 10, 2);
            
            // Código o comprobante
            $table->string('numero_transaccion')->nullable();
            
            // Comentarios
            $table->text('observacion')->nullable();
            
            // Estado del pago
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            
            // Cliente que pagó
            $table->foreignId('pagado_por')->constrained('users')->onDelete('cascade');
            
            // Usuario que aprobó o rechazó (nullable)
            $table->foreignId('validado_por')->nullable()->constrained('users')->onDelete('set null');
            
            // Fecha de validación
            $table->timestamp('validated_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
