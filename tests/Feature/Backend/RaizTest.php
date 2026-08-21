<?php

namespace Tests\Feature\Backend;

use App\Dominios\Usuarios\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La raíz encamina según haya sesión o no.
 *
 * Sustituye a la prueba del scaffold, que solo comprobaba que la página de
 * bienvenida de Laravel respondía 200. Esa página no pertenece al producto y
 * dejaba la raíz fuera de la autenticación sin que nadie lo notara.
 */
class RaizTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_sesion_la_raiz_lleva_al_formulario_de_acceso(): void
    {
        $this->get('/')->assertRedirect(route('acceder'));
    }

    public function test_con_sesion_la_raiz_lleva_al_panel_del_rol(): void
    {
        $operador = Usuario::factory()->conRol('operador')->create();
        $consulta = Usuario::factory()->conRol('consulta')->create();

        $this->actingAs($operador)->get('/')->assertRedirect(route('avance'));
        $this->actingAs($consulta)->get('/')->assertRedirect(route('pacientes'));
    }
}
