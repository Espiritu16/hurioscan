<?php

namespace Tests\Feature\Frontend;

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

    public function test_el_menu_deshabilita_las_opciones_sin_ruta_declarada(): void
    {
        // Ninguna ruta con nombre existe todavía en este sprint: todas las
        // opciones deben degradarse a deshabilitadas, no romper la vista.
        $menu = $this->blade('<x-menu-lateral rol="administrador" />');
        $menu->assertSee('aria-disabled="true"', false);
        $menu->assertDontSee('href=', false);
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
