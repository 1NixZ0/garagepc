<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de Hardware (M-05). Base de conocimiento que mantiene el
 * Administrador y sobre la que opera el Motor de Análisis Proactivo.
 * categoria_rendimiento usa la escala ordinal de cinco niveles:
 * 1 entrada, 2 gama baja, 3 gama media, 4 gama media-alta, 5 gama alta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_hardware', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['cpu', 'gpu', 'ram', 'almacenamiento', 'motherboard', 'fuente', 'refrigeracion', 'gabinete', 'otro']);
            $table->string('modelo', 150);
            $table->unsignedTinyInteger('categoria_rendimiento');
            $table->year('anio_lanzamiento');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['tipo', 'modelo']);
            $table->index('categoria_rendimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_hardware');
    }
};
