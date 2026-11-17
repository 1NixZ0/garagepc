<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alertas del Motor de Análisis Proactivo (M-06).
 *
 * componente_id es el componente señalado. componente_secundario_id solo se
 * usa en el aviso de posible desbalance, que involucra dos piezas (CPU y GPU).
 *
 * fundamento guarda el dato que originó la alerta (RF-ANA-08): ninguna alerta
 * se publica sin su fundamento verificable por el usuario.
 *
 * Las alertas las persiste la tarea programada; el Dashboard las LEE sin
 * disparar la evaluación (RF-DSH-09).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('componente_id')->constrained('componentes')->cascadeOnDelete();
            $table->foreignId('componente_secundario_id')->nullable()->constrained('componentes')->cascadeOnDelete();

            $table->enum('tipo', ['actualizacion', 'posible_desbalance', 'mantenimiento_preventivo']);
            $table->json('fundamento')->comment('Datos que originaron la alerta (RF-ANA-08)');
            $table->enum('estado', ['activa', 'vista', 'resuelta'])->default('activa');

            $table->dateTime('evaluada_en');
            $table->dateTime('vista_en')->nullable();
            $table->timestamps();

            $table->index(['componente_id', 'estado']);
            $table->index(['tipo', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
