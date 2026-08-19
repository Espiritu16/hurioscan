<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\SesionSimulada;
use Tests\TestCase;

class PantallaAccesoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_el_estado_idle_muestra_el_formulario_sin_error(): void
    {
        $vista = $this->view('dominios.usuarios.acceder', ['estado' => 'idle']);

        $vista->assertSee('Acceder');
        $vista->assertSee('Correo');
        $vista->assertSee('Contraseña');
        $vista->assertDontSee('role="alert"', false);
        $vista->assertDontSee('aria-invalid', false);
    }

    public function test_el_estado_enviando_deshabilita_el_boton(): void
    {
        $vista = $this->view('dominios.usuarios.acceder', ['estado' => 'enviando']);

        $vista->assertSee('Accediendo…', false);
        $vista->assertSee('disabled');
    }

    public function test_el_estado_de_error_anuncia_el_motivo_y_devuelve_el_foco(): void
    {
        $vista = $this->view('dominios.usuarios.acceder', [
            'estado' => 'error',
            'email' => 'operador@hurioscan.test',
        ]);

        $vista->assertSee('role="alert"', false);
        $vista->assertSee('Correo o contraseña incorrectos.');
        $vista->assertSee('aria-invalid="true"', false);
        // El foco vuelve al primer campo marcado.
        $vista->assertSee('autofocus', false);
        // El correo escrito se conserva para no obligar a retipearlo.
        $vista->assertSee('operador@hurioscan.test');
    }

    public function test_el_doble_devuelve_el_mismo_resultado_para_los_tres_fallos(): void
    {
        $sesion = new SesionSimulada;

        // Credencial inválida, usuario inexistente y usuario inactivo son
        // indistinguibles a propósito (contrato: un único NO_AUTENTICADO).
        $this->assertNull($sesion->autenticar('operador@hurioscan.test', 'incorrecta'));
        $this->assertNull($sesion->autenticar('nadie@hurioscan.test', 'hurioscan'));
        $this->assertNull($sesion->autenticar('inactivo@hurioscan.test', 'hurioscan'));
    }

    public function test_el_doble_autentica_a_los_tres_roles(): void
    {
        $sesion = new SesionSimulada;

        $this->assertSame('operador', $sesion->autenticar('operador@hurioscan.test', 'hurioscan')['rol']);
        $this->assertSame('consulta', $sesion->autenticar('consulta@hurioscan.test', 'hurioscan')['rol']);
        $this->assertSame('administrador', $sesion->autenticar('admin@hurioscan.test', 'hurioscan')['rol']);
    }

    public function test_el_destino_tras_acceder_depende_del_rol(): void
    {
        $sesion = new SesionSimulada;

        // El rol consulta no tiene acceso al panel de avance.
        $this->assertSame('pacientes', $sesion->destinoSegunRol('consulta'));
        $this->assertSame('avance', $sesion->destinoSegunRol('operador'));
        $this->assertSame('avance', $sesion->destinoSegunRol('administrador'));
    }
}
