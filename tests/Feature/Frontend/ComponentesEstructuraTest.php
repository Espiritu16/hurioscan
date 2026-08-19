<?php

namespace Tests\Feature\Frontend;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Tests\TestCase;

class ComponentesEstructuraTest extends TestCase
{
    public function test_el_menu_lateral_oculta_lo_no_permitido_para_el_rol(): void
    {
        $consulta = $this->blade('<x-menu-lateral rol="consulta" />');
        $consulta->assertSee('Búsqueda');
        $consulta->assertSee('Pacientes');
        $consulta->assertDontSee('Panel de avance');
        $consulta->assertDontSee('Usuarios');
        $consulta->assertDontSee('Auditoría');

        $operador = $this->blade('<x-menu-lateral rol="operador" />');
        $operador->assertSee('Panel de avance');
        $operador->assertSee('Hojas ilegibles');
        $operador->assertDontSee('Usuarios');

        $administrador = $this->blade('<x-menu-lateral rol="administrador" />');
        $administrador->assertSee('Usuarios');
        $administrador->assertSee('Auditoría');
    }

    /**
     * El test fabrica su propia condición vaciando la tabla de rutas, en vez
     * de apoyarse en que las rutas reales no estén montadas: eso era cierto
     * mientras la línea frontend iba por delante del montaje, y dejó de serlo
     * en cuanto las rutas existieron.
     */
    public function test_el_menu_deshabilita_las_opciones_sin_ruta_declarada(): void
    {
        $this->app['router']->setRoutes(new RouteCollection);

        $menu = $this->blade('<x-menu-lateral rol="administrador" />');

        $menu->assertSee('aria-disabled="true"', false);
        $menu->assertDontSee('href=', false);
        // Degrada, no rompe: las opciones siguen visibles.
        $menu->assertSee('Panel de avance');
    }

    public function test_el_menu_enlaza_las_opciones_cuya_ruta_si_existe(): void
    {
        $rutas = new RouteCollection;
        $rutas->add(new Route(['GET'], '/avance', ['as' => 'avance', fn () => '']));
        $this->app['router']->setRoutes($rutas);

        $menu = $this->blade('<x-menu-lateral rol="administrador" />');

        $menu->assertSee('href=', false);
        // La que existe se enlaza y la que no, se deshabilita: conviven.
        $menu->assertSee('aria-disabled="true"', false);
    }

    /**
     * Invariante que no depende de qué rutas estén montadas: cada opción del
     * menú es un enlace o está deshabilitada, nunca ninguna de las dos ni las
     * dos a la vez. Vale con el montaje completo y sin él.
     */
    public function test_ninguna_opcion_del_menu_queda_sin_resolver(): void
    {
        foreach (['operador', 'consulta', 'administrador'] as $rol) {
            $html = (string) $this->blade('<x-menu-lateral :rol="$rol" />', ['rol' => $rol]);

            preg_match_all('/<a\b[^>]*>/', $html, $coincidencias);
            $this->assertNotEmpty($coincidencias[0], "el rol {$rol} no vio ninguna opción");

            foreach ($coincidencias[0] as $ancla) {
                $enlaza = str_contains($ancla, 'href=');
                $deshabilitada = str_contains($ancla, 'aria-disabled="true"');

                $this->assertTrue(
                    $enlaza xor $deshabilitada,
                    "opción sin resolver para el rol {$rol}: {$ancla}",
                );
            }
        }
    }

    public function test_la_barra_de_paciente_es_fija_y_muestra_la_marca_de_sesion(): void
    {
        $barra = $this->blade(
            '<x-barra-paciente nombre="Mamani Choque, Rosa Elena" historia="04-118-2297" />'
        );

        $barra->assertSee('sticky');
        $barra->assertSee('Fijado para esta sesión');
        $barra->assertSee('Mamani Choque, Rosa Elena');
        $barra->assertSee('04-118-2297');
    }

    public function test_el_indicador_de_paso_marca_el_paso_actual(): void
    {
        $indicador = $this->blade(
            '<x-indicador-paso :pasos="[\'Paciente\', \'Captura\', \'Revisión\', \'Cierre\']" :actual="2" />'
        );

        $indicador->assertSee('aria-current="step"', false);
        $indicador->assertSee('Paso 2 de 4');
        $indicador->assertSeeInOrder(['Paciente', 'Captura', 'Revisión', 'Cierre']);
    }
}
