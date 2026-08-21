<?php

namespace App\Dominios\Usuarios;

use App\Compartido\Persistencia\InstantesEnUtc;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Entidad Usuario (`docs/persistencia/modelo.md`, RF-011).
 *
 * Vive dentro de su dominio y no en una carpeta técnica compartida: es la misma
 * regla domain-first que `docs/contratos/servicios-aplicacion.md` fija para las
 * interfaces.
 *
 * Un usuario tiene **un solo rol**. La matriz de permisos contempla varios,
 * pero el schema admite uno; habilitar varios exige una tabla `usuario_rol` y
 * reabre el modelo (aprobado explícitamente por Kevin el 2026-08-19).
 */
#[Fillable(['nombre', 'email', 'password', 'rol', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, InstantesEnUtc, SoftDeletes;

    public const ROLES = ['operador', 'consulta', 'administrador'];

    protected $table = 'usuarios';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * El descubrimiento automático de factories asume `App\Models`; al vivir el
     * modelo en su dominio hay que decir cuál es la suya.
     */
    protected static function newFactory(): Factory
    {
        return UsuarioFactory::new();
    }
}
