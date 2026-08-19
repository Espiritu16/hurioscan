<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Pacientes\ServicioPacientesDoble;
use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Pacientes\Componentes\BuscadorPacientes;
use App\Dominios\Pacientes\Componentes\FormularioPaciente;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;
use Livewire\Livewire;
use Tests\TestCase;

class PacientesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->app->singleton(ServicioPacientes::class, ServicioPacientesDoble::class);
    }

    public function test_la_busqueda_muestra_resultados(): void
    {
        Livewire::test(BuscadorPacientes::class)
            ->assertSet('estado', 'exito')
            ->assertSee('Mamani Choque')
            ->assertSee('04-118-2297');
    }

    public function test_un_termino_sin_coincidencias_muestra_estado_vacio_con_salida(): void
    {
        Livewire::test(BuscadorPacientes::class)
            ->set('termino', 'zzzz-no-existe')
            ->assertSet('estado', 'vacio')
            // Estado vacío, nunca un error.
            ->assertSee('No encontramos pacientes con ese dato')
            ->assertSee('Registrar paciente nuevo')
            ->assertDontSee('role="alert"', false);
    }

    public function test_el_rol_consulta_no_ve_la_accion_de_registrar(): void
    {
        Livewire::test(BuscadorPacientes::class, ['rol' => 'consulta'])
            ->assertDontSee('Registrar paciente nuevo')
            ->assertSee('Ver línea de tiempo');

        Livewire::test(BuscadorPacientes::class, ['rol' => 'operador'])
            ->assertSee('Registrar paciente nuevo');
    }

    public function test_cambiar_el_termino_devuelve_la_pagina_a_uno(): void
    {
        Livewire::test(BuscadorPacientes::class)
            ->set('pagina', 3)
            ->set('termino', 'Mamani')
            ->assertSet('pagina', 1);
    }

    public function test_un_dni_ya_registrado_no_continua_con_el_alta(): void
    {
        Livewire::test(FormularioPaciente::class)
            ->set('dni', '41822703')
            ->call('consultarDni')
            ->assertSet('estadoConsulta', 'ya_registrado')
            ->assertSee('Este documento ya está registrado.')
            ->assertSee('04-118-2297')
            ->assertSee('Abrir su folder')
            // El formulario de alta no se ofrece: el paciente ya existe.
            ->assertDontSee('Guardar paciente');
    }

    public function test_un_dni_del_proveedor_precarga_campos_editables(): void
    {
        Livewire::test(FormularioPaciente::class)
            ->set('dni', '70112233')
            ->call('consultarDni')
            ->assertSet('estadoConsulta', 'precargado')
            ->assertSet('apellidos', 'Quispe Mamani')
            ->assertSet('nombres', 'Ana Lucía')
            ->assertSet('origenDatos', 'proveedor')
            // Los campos siguen disponibles para corregirlos.
            ->assertSee('Guardar paciente');
    }

    public function test_editar_lo_precargado_devuelve_el_origen_a_manual(): void
    {
        Livewire::test(FormularioPaciente::class)
            ->set('dni', '70112233')
            ->call('consultarDni')
            ->assertSet('origenDatos', 'proveedor')
            ->set('apellidos', 'Quispe Mamani de Torres')
            ->assertSet('origenDatos', 'manual');
    }

    public function test_un_dni_inexistente_deja_el_formulario_utilizable(): void
    {
        Livewire::test(FormularioPaciente::class)
            ->set('dni', '70000001')
            ->call('consultarDni')
            ->assertSet('estadoConsulta', 'no_encontrado')
            ->assertSee('Escribe los datos a mano')
            ->assertSee('Guardar paciente');
    }

    public function test_el_proveedor_caido_deja_el_formulario_utilizable_sin_jerga(): void
    {
        $componente = Livewire::test(FormularioPaciente::class)
            ->set('dni', '70999999')
            ->call('consultarDni')
            ->assertSet('estadoConsulta', 'proveedor_no_disponible')
            ->assertSee('Escribe los datos a mano')
            ->assertSee('Guardar paciente');

        // El motivo técnico no se expone al operador.
        $componente->assertDontSee('token');
        $componente->assertDontSee('crédito');
    }

    public function test_una_historia_clinica_duplicada_se_explica_sin_perder_lo_escrito(): void
    {
        Livewire::test(FormularioPaciente::class)
            ->set('numeroHistoria', '04-118-2297')
            ->set('apellidos', 'Prueba')
            ->set('nombres', 'Caso')
            ->call('guardar')
            ->assertSet('guardado', false)
            ->assertSee('Ya existe un paciente con ese número de historia clínica.')
            ->assertSet('apellidos', 'Prueba');
    }

    public function test_el_doble_cubre_los_cuatro_desenlaces_del_dni(): void
    {
        $pacientes = new ServicioPacientesDoble;

        // 1. Paciente ya registrado: no se consulta al proveedor.
        $this->assertNotNull($pacientes->consultarDni('41822703')['pacienteExistente']);

        // 2. Datos traídos del proveedor.
        $this->assertSame('proveedor', $pacientes->consultarDni('70112233')['datos']['origen']);

        // 3. DNI inexistente. 4. Proveedor caído.
        foreach ([['70000001', 'IDENTIDAD_NO_ENCONTRADA'], ['70999999', 'IDENTIDAD_PROVEEDOR_NO_DISPONIBLE']] as [$dni, $codigo]) {
            try {
                $pacientes->consultarDni($dni);
                $this->fail("Se esperaba {$codigo}");
            } catch (ErrorDeAplicacion $e) {
                $this->assertSame($codigo, $e->getCodigo());
            }
        }
    }
}
