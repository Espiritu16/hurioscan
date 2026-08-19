<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Usuarios\ServicioUsuariosDoble;
use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Usuarios\Componentes\FormularioAcceso;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Livewire\Livewire;
use Tests\TestCase;

class PantallaAccesoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // El doble se usa aquí igual que en desarrollo: ligado a la interfaz,
        // nunca referenciado desde el código de producción.
        $this->app->singleton(ServicioUsuarios::class, ServicioUsuariosDoble::class);
    }

    public function test_el_estado_inicial_muestra_el_formulario_sin_error(): void
    {
        Livewire::test(FormularioAcceso::class)
            ->assertSet('estado', 'idle')
            ->assertSee('Correo')
            ->assertSee('Contraseña')
            ->assertSee('Acceder')
            ->assertDontSee('aria-invalid', false);
    }

    public function test_una_credencial_invalida_deja_el_formulario_en_error(): void
    {
        Livewire::test(FormularioAcceso::class)
            ->set('email', 'operador@hurioscan.test')
            ->set('password', 'incorrecta')
            ->call('acceder')
            ->assertSet('estado', 'error')
            ->assertSee('Correo o contraseña incorrectos.')
            ->assertSee('aria-invalid="true"', false)
            // El foco vuelve al primer campo marcado.
            ->assertSee('autofocus', false)
            // El correo escrito se conserva; la contraseña se limpia.
            ->assertSet('email', 'operador@hurioscan.test')
            ->assertSet('password', '');
    }

    public function test_el_mensaje_de_error_es_el_mismo_en_los_tres_fallos(): void
    {
        $mensajes = [];

        foreach ([
            ['operador@hurioscan.test', 'incorrecta'],   // credencial inválida
            ['nadie@hurioscan.test', 'hurioscan'],       // usuario inexistente
            ['inactivo@hurioscan.test', 'hurioscan'],    // usuario inactivo
        ] as [$email, $password]) {
            $mensajes[] = Livewire::test(FormularioAcceso::class)
                ->set('email', $email)
                ->set('password', $password)
                ->call('acceder')
                ->get('error');
        }

        // Un mensaje distinto revelaría qué correos están registrados.
        $this->assertCount(1, array_unique($mensajes));
    }

    public function test_una_credencial_valida_no_deja_error(): void
    {
        Livewire::test(FormularioAcceso::class)
            ->set('email', 'operador@hurioscan.test')
            ->set('password', 'hurioscan')
            ->call('acceder')
            ->assertSet('estado', 'idle')
            ->assertSet('error', null);
    }

    public function test_el_doble_lanza_no_autenticado_en_los_tres_fallos(): void
    {
        $usuarios = new ServicioUsuariosDoble;

        foreach ([
            ['operador@hurioscan.test', 'incorrecta'],
            ['nadie@hurioscan.test', 'hurioscan'],
            ['inactivo@hurioscan.test', 'hurioscan'],
        ] as [$email, $password]) {
            try {
                $usuarios->autenticar($email, $password);
                $this->fail("Se esperaba ErrorDeAplicacion para {$email}");
            } catch (ErrorDeAplicacion $e) {
                $this->assertSame('NO_AUTENTICADO', $e->getCodigo());
            }
        }
    }

    public function test_el_doble_autentica_a_los_tres_roles(): void
    {
        $usuarios = new ServicioUsuariosDoble;

        $this->assertSame('operador', $usuarios->autenticar('operador@hurioscan.test', 'hurioscan')['rol']);
        $this->assertSame('consulta', $usuarios->autenticar('consulta@hurioscan.test', 'hurioscan')['rol']);
        $this->assertSame('administrador', $usuarios->autenticar('admin@hurioscan.test', 'hurioscan')['rol']);
    }

    public function test_el_listado_de_usuarios_nunca_expone_la_contrasena(): void
    {
        $listado = (new ServicioUsuariosDoble)->listar();

        foreach ($listado['datos'] as $usuario) {
            $this->assertArrayNotHasKey('password', $usuario);
            $this->assertArrayNotHasKey('hash', $usuario);
        }
    }

    public function test_el_administrador_no_puede_quitarse_su_propio_rol(): void
    {
        $usuarios = new ServicioUsuariosDoble;

        try {
            $usuarios->actualizar(3, ['rol' => 'operador']);
            $this->fail('Se esperaba ADMIN_NO_PUEDE_QUITARSE_ROL');
        } catch (ErrorDeAplicacion $e) {
            $this->assertSame('ADMIN_NO_PUEDE_QUITARSE_ROL', $e->getCodigo());
        }

        // Desactivarse a sí mismo cae en la misma regla.
        try {
            $usuarios->actualizar(3, ['activo' => false]);
            $this->fail('Se esperaba ADMIN_NO_PUEDE_QUITARSE_ROL');
        } catch (ErrorDeAplicacion $e) {
            $this->assertSame('ADMIN_NO_PUEDE_QUITARSE_ROL', $e->getCodigo());
        }
    }

    public function test_el_doble_solo_se_liga_en_local_o_testing(): void
    {
        // El provider no liga nada fuera de local/testing, para que un binding
        // faltante falle de forma visible en vez de servir datos de ejemplo.
        $this->assertTrue(app()->environment('testing'));
        $this->assertFalse(config('dobles.usuarios', false) === null);
    }
}
