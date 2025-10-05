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
        Schema::create('turnos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->comment('Cajero responsable');
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_inicial', 10, 2)->comment('Fondo de caja al abrir');
            $table->decimal('monto_final', 10, 2)->nullable()->comment('Total al cerrar');
            $table->decimal('total_ventas', 10, 2)->default(0)->comment('Total vendido en turno');
            $table->decimal('total_efectivo', 10, 2)->default(0);
            $table->decimal('total_tarjeta', 10, 2)->default(0);
            $table->decimal('total_transferencia', 10, 2)->default(0);
            $table->decimal('total_deuna', 10, 2)->default(0)->comment('Total pagos con Deuna');
            $table->decimal('diferencia', 10, 2)->default(0)->comment('Faltante/sobrante');
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->timestamps();
            
            $table->index('usuario_id');
            $table->index('fecha_apertura');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos_caja');
    }
};
