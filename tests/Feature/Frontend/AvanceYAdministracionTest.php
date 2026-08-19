<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Digitalizacion\ServicioDigitalizacionDoble;
use App\Compartido\Dobles\Usuarios\ServicioUsuariosDoble;
use App\Dominios\Digitalizacion\Componentes\PanelAvance;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use App\Dominios\Usuarios\Componentes\AdministracionUsuarios;
use App\Dominios\Usuarios\Componentes\ConsultaAuditoria;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Livewire\Livewire;
use Tests\TestCase;

class AvanceYAdministracionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->app->singleton(ServicioDigitalizacion::class, ServicioDigitalizacionDoble::class);
        $this->app->singleton(ServicioUsuarios::class, ServicioUsuariosDoble::class);
    }

    public function test_sin_total_configurado_no_se_muestra_porcentaje_ni_barra(): void
    {
        $componente = Livewire::test(PanelAvance::class)
            ->assertSet('estado', 'exito');

        $this->assertFalse($componente->get('hayPorcentaje'));

        $componente
            ->assertSee('Total del acervo sin configurar')
            // El avance absoluto sí se muestra.
            ->assertSee('1 248')
            // Un 0 % sería engañoso: no aparece ningún porcentaje.
            ->assertDontSee('0 % de la meta')
            ->assertDontSee('% de la meta');
    }

    public function test_el_panel_muestra_los_agregados_y_las_sesiones_recientes(): void
    {
        Livewire::test(PanelAvance::class)
            ->assertSee('Hojas procesadas')
            ->assertSee('18 942')
            ->assertSee('Hojas ilegibles')
            ->assertSee('Ritmo semanal')
            ->assertSee('Mamani Choque');
    }

    public function test_el_listado_de_usuarios_no_muestra_ningun_hash(): void
    {
        $html = Livewire::test(AdministracionUsuarios::class)
            ->assertSet('estado', 'exito')
            ->html();

        // Lo que no puede aparecer es una credencial de un usuario existente:
        // ni el hash bcrypt ni ningún valor de contraseña. El formulario de
        // alta sí tiene un campo de contraseña, vacío, y eso es correcto.
        $this->assertStringNotContainsString('$2y$', $html);
        $this->assertStringNotContainsString('hurioscan"', $html);
        // `[^&]` y no `.` : el valor vacío del formulario es `&quot;&quot;`, y
        // un comodín cruzaría hasta el campo siguiente dando un falso positivo.
        $this->assertDoesNotMatchRegularExpression('/password&quot;:&quot;[^&]/', $html);
    }

    public function test_quitarse_el_propio_rol_de_administrador_explica_por_que_se_rechaza(): void
    {
        Livewire::test(AdministracionUsuarios::class)
            // El id 3 es el administrador de la sesión de ejemplo.
            ->call('cambiarRol', 3, 'operador')
            ->assertSee('No puedes quitarte a ti mismo el rol de administrador')
            ->assertSee('el sistema quedaría sin ningún administrador activo');
    }

    public function test_desactivarse_a_si_mismo_cae_en_la_misma_regla(): void
    {
        Livewire::test(AdministracionUsuarios::class)
            ->call('cambiarActividad', 3, false)
            ->assertSee('No puedes quitarte a ti mismo el rol de administrador');
    }

    public function test_cambiar_el_rol_de_otro_usuario_si_funciona(): void
    {
        $componente = Livewire::test(AdministracionUsuarios::class)
            ->call('cambiarRol', 1, 'consulta')
            ->assertSee('Rol actualizado.');

        $usuario = collect($componente->get('usuarios'))->firstWhere('id', 1);
        $this->assertSame('consulta', $usuario['rol']);
    }

    public function test_la_auditoria_solo_muestra_los_campos_de_la_allowlist(): void
    {
        $html = Livewire::test(ConsultaAuditoria::class)
            ->assertSet('estado', 'exito')
            ->html();

        // Los campos permitidos están.
        $this->assertStringContainsString('Documento', $html);
        $this->assertStringContainsString('consultar', $html);
        // Ningún texto de documento ni contraseña.
        $this->assertStringNotContainsString('textoExtraido', $html);
        $this->assertStringNotContainsString('DIAGNÓSTICO', $html);
        $this->assertStringNotContainsString('password', $html);
    }

    public function test_los_filtros_de_auditoria_viajan_en_la_url_y_filtran(): void
    {
        Livewire::withUrlParams(['entidad' => 'Paciente'])
            ->test(ConsultaAuditoria::class)
            ->assertSet('entidad', 'Paciente')
            ->assertCount('filas', 1)
            ->assertSee('crear');
    }

    public function test_una_accion_del_sistema_se_muestra_sin_usuario(): void
    {
        Livewire::test(ConsultaAuditoria::class)
            ->set('accion', 'actualizar')
            ->assertSee('Sistema');
    }

    public function test_un_filtro_sin_resultados_ofrece_quitarlo(): void
    {
        Livewire::test(ConsultaAuditoria::class)
            ->set('entidad', 'Usuario')
            ->assertSet('estado', 'vacio')
            ->assertSee('Quitar los filtros');
    }
}
