<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cotizador Multimoneda (M-07).
 *
 * Se persiste cada cotización con su fecha y hora de obtención (RF-COT-03),
 * de modo que el sistema pueda advertir cuando supera las 24 horas y
 * abstenerse de convertir cuando supera los 7 días (RF-COT-11).
 *
 * Las paridades se expresan contra el dólar como moneda pivote.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->decimal('ars_por_usd', 14, 6);
            $table->decimal('brl_por_usd', 14, 6);
            $table->string('origen', 80)->comment('Servicio externo que proveyó la paridad');
            $table->dateTime('obtenida_en');
            $table->timestamps();

            $table->index('obtenida_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
