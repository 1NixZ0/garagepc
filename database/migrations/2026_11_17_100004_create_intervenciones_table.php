<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora de Mantenimiento (M-03). Historial de intervenciones técnicas
 * por componente. Alimenta la alerta de mantenimiento preventivo (RF-ANA-07).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervenciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('componente_id')->constrained('componentes')->cascadeOnDelete();
            $table->enum('tipo', ['limpieza', 'pasta_termica', 'inspeccion', 'upgrade', 'reparacion', 'otro']);
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->timestamps();

            $table->index(['componente_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervenciones');
    }
};
