<?php

namespace Tests\Feature\Backend;

use App\Dominios\Usuarios\Usuario;
use Database\Seeders\UsuariosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UT-04 — existe un usuario de cada rol para que F01 pueda probar la
 * navegación.
 */
class SeederDeUsuariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_hay_un_usuario_activo_por_cada_rol_y_entra_de_verdad(): void
    {
        $this->seed(UsuariosSeeder::class);

        foreach (Usuario::ROLES as $rol) {
            $usuario = Usuario::query()->where('rol', $rol)->where('activo', true)->first();

            $this->assertNotNull($usuario, "falta un usuario activo con rol {$rol}");

            // No basta con que la fila exista: tiene que poder entrar. Una
            // contraseña mal sembrada dejaría el seeder «correcto» e inútil.
            $this->post('/acceder', [
                'email' => $usuario->email,
                'password' => UsuariosSeeder::CLAVE_DE_DESARROLLO,
            ])->assertRedirect();

            $this->assertAuthenticatedAs($usuario);
            $this->post('/salir');
        }
    }

    /** El seeder se puede volver a correr sin duplicar ni fallar. */
    public function test_el_seeder_es_repetible(): void
    {
        $this->seed(UsuariosSeeder::class);
        $this->seed(UsuariosSeeder::class);

        $this->assertSame(1, Usuario::query()->where('email', 'operador@hurioscan.test')->count());
    }
}
