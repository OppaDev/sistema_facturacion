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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_venta', 50)->unique()->comment('V-001, V-002, etc');
            $table->dateTime('fecha_venta');
            $table->foreignId('usuario_id')->constrained('users')->comment('Cajero que realizó la venta');
            $table->enum('tipo_pago', ['efectivo', 'tarjeta', 'transferencia', 'deuna', 'mixto'])->default('efectivo');
            $table->decimal('monto_efectivo', 10, 2)->default(0);
            $table->decimal('monto_tarjeta', 10, 2)->default(0);
            $table->decimal('monto_transferencia', 10, 2)->default(0);
            $table->decimal('monto_deuna', 10, 2)->default(0)->comment('Pago con app Deuna');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('iva', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('cliente_nombre')->nullable()->comment('Opcional para ticket');
            $table->string('cliente_identificacion', 20)->nullable();
            $table->text('notas')->nullable();
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->foreignId('turno_id')->nullable()->constrained('turnos_caja')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('fecha_venta');
            $table->index('usuario_id');
            $table->index('turno_id');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
