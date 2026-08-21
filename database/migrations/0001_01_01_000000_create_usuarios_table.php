<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MIG-001 — tabla `usuarios` (entidad Usuario, RF-011).
 *
 * El plan de `docs/persistencia/modelo.md` describe MIG-001 como «agregar
 * `rol`, `activo` y `deleted_at` a la tabla `users` del scaffold, renombrada a
 * `usuarios`». Se materializa reescribiendo la propia migración del scaffold en
 * vez de encadenarle un `ALTER` posterior: el proyecto no tiene datos —el plan
 * lo dice explícitamente— y una tabla creada ya con su forma final se lee y se
 * revierte mejor que una creada mal y corregida a continuación. El estado final
 * del schema es el que aprobó Arquitectura, que es lo que MIG-001 fija.
 *
 * Los instantes usan `timestamptz` (RNF-005): PostgreSQL guarda el momento
 * absoluto y la conversión a hora de Perú ocurre solo al mostrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('email', 180)->unique();
            $table->string('password');
            $table->string('rol', 20);
            $table->boolean('activo')->default(true);
            // No es un campo del dominio: lo exige el `recordar` de
            // `POST /acceder`, que Laravel resuelve con este token.
            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('rol');
        });

        $this->restringirRol();

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // `user_id` lo escribe el manejador de sesiones de Laravel con
            // ese nombre exacto: es infraestructura del framework, no un
            // campo del dominio, y renombrarlo rompe las sesiones en base.
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }

    /**
     * El conjunto cerrado de `rol` se declara como `CHECK` en el motor, tal
     * como exige el modelo: un enum nativo obligaría a migrar el tipo para
     * cambiarlo.
     *
     * SQLite no admite agregar restricciones con `ALTER TABLE`, así que ahí la
     * restricción no existe. No es una laguna silenciosa: SQLite es solo la
     * base de retroalimentación rápida de la suite; el motor del proyecto es
     * PostgreSQL (AGENTS.md), y sobre él corre la suite Feature en CI. La
     * validación de aplicación rechaza igual un rol fuera del conjunto en
     * ambos motores.
     */
    private function restringirRol(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(
            "ALTER TABLE usuarios ADD CONSTRAINT usuarios_rol_check
             CHECK (rol IN ('operador', 'consulta', 'administrador'))"
        );
    }
};
