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
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos_caja')->onDelete('cascade');
            $table->enum('tipo', ['ingreso', 'egreso', 'ajuste']);
            $table->string('concepto')->comment('Pago a proveedor, Gastos varios, etc');
            $table->decimal('monto', 10, 2);
            $table->foreignId('usuario_id')->constrained('users');
            $table->text('notas')->nullable();
            $table->timestamps();
            
            $table->index('turno_id');
            $table->index('tipo');
            $table->index('usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
