<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de Juegos. Vocabulario común para el Seguimiento de Rendimiento.
 *
 * Sin un catálogo, el mismo juego escrito de tres maneras distintas genera
 * tres configuraciones separadas y ningún promedio histórico sirve. Lo mantiene
 * el Administrador, igual que el Catálogo de Hardware.
 *
 * exigencia usa una escala ordinal de cinco niveles:
 * 1 muy liviano, 2 liviano, 3 medio, 4 exigente, 5 muy exigente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_juegos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120)->unique();
            $table->unsignedTinyInteger('exigencia');
            $table->year('anio_lanzamiento');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('exigencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_juegos');
    }
};
