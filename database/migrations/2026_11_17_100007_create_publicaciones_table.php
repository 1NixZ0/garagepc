<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marketplace Interno (M-08).
 *
 * precio_sugerido es el valor estimado de reventa calculado por el sistema
 * (RF-COT-09). precio_publicado es el que finalmente fija el usuario, que
 * puede modificarlo (RF-MKT-02).
 *
 * El sistema no interviene en el pago: solo pone en contacto a las partes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('componente_id')->constrained('componentes')->cascadeOnDelete();

            $table->decimal('precio_sugerido', 12, 2)->nullable();
            $table->decimal('precio_publicado', 12, 2);
            $table->enum('moneda', ['ARS', 'USD', 'BRL']);
            $table->enum('condicion', ['excelente', 'muy_bueno', 'bueno', 'regular']);
            $table->text('descripcion')->nullable();
            $table->string('contacto', 150);
            $table->boolean('mostrar_bitacora')->default(true);

            $table->enum('estado', ['activa', 'despublicada', 'moderada'])->default('activa');
            $table->text('motivo_moderacion')->nullable();
            $table->dateTime('publicada_en');
            $table->dateTime('finalizada_en')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('componente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
