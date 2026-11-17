<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de depreciación por categoría de componente (RF-CAT-10).
 * La mantiene el Administrador. Es lo que permite estimar el valor de
 * reventa, que es un problema distinto de convertir monedas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factores_depreciacion', function (Blueprint $table) {
            $table->id();
            $table->enum('categoria', ['cpu', 'gpu', 'ram', 'almacenamiento', 'motherboard', 'fuente', 'refrigeracion', 'gabinete', 'otro']);
            $table->decimal('porcentaje_anual', 5, 2)->comment('Pérdida de valor anual, en porcentaje');
            $table->timestamps();

            $table->unique('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factores_depreciacion');
    }
};
