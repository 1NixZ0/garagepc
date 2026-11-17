<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seguimiento de Rendimiento (M-04).
 *
 * La terna juego + resolucion + nivel_calidad define la CONFIGURACIÓN.
 * El promedio histórico se calcula por configuración y nunca mezclando
 * configuraciones distintas: sin esa normalización, la regla de degradación
 * dispararía una falsa alerta cada vez que el usuario probara un juego nuevo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_rendimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();

            $table->string('juego', 120);
            $table->enum('resolucion', ['1080p', '1440p', '2160p']);
            $table->enum('nivel_calidad', ['bajo', 'medio', 'alto', 'ultra']);

            $table->decimal('fps_promedio', 6, 2);
            $table->decimal('temp_cpu', 5, 2)->nullable();
            $table->decimal('temp_gpu', 5, 2)->nullable();

            $table->dateTime('fecha_sesion');
            $table->timestamps();

            $table->index(['equipo_id', 'juego', 'resolucion', 'nivel_calidad'], 'idx_configuracion');
            $table->index('fecha_sesion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_rendimiento');
    }
};
