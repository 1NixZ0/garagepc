<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula la sesión con el Catálogo de Juegos.
 *
 * Es nullable por el mismo motivo que catalogo_hardware_id en componentes:
 * el usuario puede registrar un juego que no esté catalogado. El campo
 * 'juego' sigue siendo el valor canónico que define la configuración; este
 * identificador es el puente hacia los datos del catálogo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesiones_rendimiento', function (Blueprint $table) {
            $table->foreignId('catalogo_juego_id')
                  ->nullable()
                  ->after('equipo_id')
                  ->constrained('catalogo_juegos')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sesiones_rendimiento', function (Blueprint $table) {
            $table->dropConstrainedForeignId('catalogo_juego_id');
        });
    }
};
