<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coeficientes de ajuste según la condición declarada del componente
 * (RF-CAT-11). Los mantiene el Administrador y se aplican sobre el
 * precio ya depreciado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coeficientes_condicion', function (Blueprint $table) {
            $table->id();
            $table->enum('condicion', ['excelente', 'muy_bueno', 'bueno', 'regular']);
            $table->decimal('coeficiente', 4, 3)->comment('Multiplicador sobre el precio depreciado');
            $table->timestamps();

            $table->unique('condicion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coeficientes_condicion');
    }
};
