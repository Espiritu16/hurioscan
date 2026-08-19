<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Documentos\ServicioDocumentosDoble;
use App\Compartido\Dobles\Pacientes\ServicioPacientesDoble;
use App\Dominios\Documentos\Componentes\BusquedaContenido;
use App\Dominios\Documentos\Componentes\VisorDocumento;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use App\Dominios\Pacientes\Componentes\LineaDeTiempo;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultaArchivoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->app->singleton(ServicioDocumentos::class, ServicioDocumentosDoble::class);
        $this->app->singleton(ServicioPacientes::class, ServicioPacientesDoble::class);
    }

    public function test_el_termino_aparece_resaltado_en_el_fragmento(): void
    {
        Livewire::test(BusquedaContenido::class)
            ->set('termino', 'hipertensión')
            ->call('buscar')
            ->assertSet('estado', 'exito')
            ->assertSee('<mark', false)
            ->assertSee('Enalapril');
    }

    /**
     * RNF-012: el resaltado se aplica sobre texto ya escapado. Uno de los
     * resultados trae un payload dentro del fragmento.
     */
    public function test_el_resaltado_no_deja_ejecutar_html_del_documento(): void
    {
        $html = Livewire::test(BusquedaContenido::class)
            ->set('termino', 'hipertensión')
            ->call('buscar')
            ->html();

        $this->assertStringNotContainsString('<script>alert("xss")</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_un_termino_sin_coincidencias_muestra_vacio_no_error(): void
    {
        Livewire::test(BusquedaContenido::class)
            ->set('termino', 'zzzznoexiste')
            ->call('buscar')
            ->assertSet('estado', 'vacio')
            ->assertSee('Ningún documento contiene ese término')
            ->assertDontSee('role="alert"', false);
    }

    public function test_un_termino_vacio_pide_escribir_algo(): void
    {
        Livewire::test(BusquedaContenido::class)
            ->set('termino', '')
            ->call('buscar')
            ->assertSee('Escribe qué quieres buscar');
    }

    public function test_el_termino_y_la_pagina_viajan_en_la_url(): void
    {
        // Volver atrás conserva la vista porque el estado vive en la URL.
        Livewire::withUrlParams(['q' => 'hipertensión', 'pagina' => 1])
            ->test(BusquedaContenido::class)
            ->assertSet('termino', 'hipertensión')
            ->assertSet('estado', 'exito');
    }

    public function test_cambiar_un_filtro_actualiza_lista_y_conteo_en_una_accion(): void
    {
        $componente = Livewire::test(LineaDeTiempo::class);

        $totalSinFiltro = $componente->get('total');
        $this->assertSame(4, $totalSinFiltro);

        // Un solo `set`: no hace falta un segundo clic para reconsultar.
        $componente->set('tipo', 'receta');

        $this->assertSame(1, $componente->get('total'));
        $this->assertCount(1, $componente->get('documentos'));
    }

    public function test_los_filtros_de_la_linea_de_tiempo_viajan_en_la_url(): void
    {
        Livewire::withUrlParams(['tipo' => 'laboratorio'])
            ->test(LineaDeTiempo::class)
            ->assertSet('tipo', 'laboratorio')
            ->assertSet('total', 1);
    }

    public function test_un_rango_de_fechas_excluye_los_documentos_sin_fecha(): void
    {
        Livewire::test(LineaDeTiempo::class)
            ->set('desde', '2019-01-01')
            ->assertSet('total', 3);   // el documento sin fecha queda fuera
    }

    public function test_la_linea_de_tiempo_sin_resultados_ofrece_quitar_filtros(): void
    {
        Livewire::test(LineaDeTiempo::class)
            ->set('desde', '2030-01-01')
            ->assertSet('estado', 'vacio')
            ->assertSee('Quitar los filtros');
    }

    public function test_el_visor_muestra_el_documento_con_su_texto(): void
    {
        Livewire::test(VisorDocumento::class, ['documentoId' => 8142])
            ->assertSet('estado', 'exito')
            ->assertSee('Hipertensión arterial esencial')
            ->assertSee('04-118-2297');
    }

    public function test_un_documento_ilegible_dice_que_no_tiene_texto(): void
    {
        Livewire::test(VisorDocumento::class, ['documentoId' => 8144])
            ->assertSet('estado', 'exito')
            ->assertSee('no tiene texto asociado')
            ->assertSee('La imagen sigue disponible');
    }

    public function test_un_documento_fuera_de_alcance_no_revela_si_existe(): void
    {
        Livewire::test(VisorDocumento::class, ['documentoId' => 999999])
            ->assertSet('estado', 'error')
            ->assertSee('No encontramos ese documento')
            // No se distingue "no existe" de "no autorizado".
            ->assertDontSee('autoriz');
    }
}
