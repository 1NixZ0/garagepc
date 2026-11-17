<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Componente del Garaje Virtual (M-02).
 *
 * cotizacion_compra guarda la paridad vigente a la fecha de compra (RF-GAR-13),
 * de modo que el costo histórico del equipo no varíe ante fluctuaciones
 * posteriores del tipo de cambio.
 *
 * catalogo_hardware_id es nullable a propósito: CU-08 contempla componentes
 * no catalogados, que quedan fuera del análisis de antigüedad y de desbalance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('catalogo_hardware_id')->nullable()->constrained('catalogo_hardware')->nullOnDelete();

            $table->enum('categoria', ['cpu', 'gpu', 'ram', 'almacenamiento', 'motherboard', 'fuente', 'refrigeracion', 'gabinete', 'otro']);
            $table->string('modelo', 150);

            $table->date('fecha_compra');
            $table->decimal('precio_compra', 12, 2);
            $table->enum('moneda_compra', ['ARS', 'USD', 'BRL']);
            $table->decimal('cotizacion_compra', 14, 6)
                  ->comment('Paridad a USD vigente a la fecha de compra (RF-GAR-13)');

            $table->enum('estado', ['activo', 'en_reparacion', 'retirado'])->default('activo');
            $table->enum('condicion_declarada', ['excelente', 'muy_bueno', 'bueno', 'regular'])->nullable();
            $table->text('notas')->nullable();
            $table->string('imagen')->nullable();

            $table->timestamps();

            $table->index(['equipo_id', 'categoria']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes');
    }
};
