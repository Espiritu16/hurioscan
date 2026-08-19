<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Digitalizacion\ServicioDigitalizacionDoble;
use App\Compartido\Dobles\Documentos\ServicioDocumentosDoble;
use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Componentes\CierreSesion;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use App\Dominios\Documentos\Componentes\HojasIlegibles;
use App\Dominios\Documentos\Componentes\RevisionOcr;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use Livewire\Livewire;
use Tests\TestCase;

class RevisionYCierreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->app->singleton(ServicioDocumentos::class, ServicioDocumentosDoble::class);
        $this->app->singleton(ServicioDigitalizacion::class, ServicioDigitalizacionDoble::class);
    }

    /**
     * RNF-012: el texto extraído se muestra escapado, nunca ejecutado. La
     * hoja 8141 trae un `<script>` dentro del texto a propósito.
     */
    public function test_el_texto_extraido_se_muestra_escapado(): void
    {
        $html = Livewire::test(RevisionOcr::class)
            ->call('irA', 1)
            ->assertSet('texto', ServicioDocumentosDoble::PAYLOAD_XSS)
            ->html();

        // El payload aparece escapado, no como etiqueta ejecutable.
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_una_hoja_pendiente_de_ocr_lo_dice_en_vez_de_un_panel_vacio(): void
    {
        Livewire::test(RevisionOcr::class)
            ->call('irA', 0)
            ->assertSee('El OCR de esta hoja todavía está corriendo')
            // No se ofrece editar lo que todavía no existe.
            ->assertDontSee('Guardar corrección');
    }

    public function test_una_hoja_ilegible_dice_que_no_tiene_texto(): void
    {
        Livewire::test(RevisionOcr::class)
            ->call('irA', 4)
            ->assertSee('no tiene texto asociado');
    }

    public function test_un_conflicto_de_version_muestra_el_texto_vigente_y_deja_decidir(): void
    {
        $componente = Livewire::test(RevisionOcr::class)
            ->call('irA', 3)   // documento 8143, el del conflicto
            ->set('texto', 'Mi corrección local')
            ->call('guardar');

        $componente
            ->assertSet('textoEnConflicto', ServicioDocumentosDoble::TEXTO_VIGENTE)
            ->assertSee('Alguien más corrigió esta hoja')
            ->assertSee('Usar el texto vigente')
            ->assertSee('Conservar el mío')
            // No se reenvió en silencio: el texto local sigue intacto.
            ->assertSet('texto', 'Mi corrección local');
    }

    public function test_tomar_el_texto_vigente_es_una_decision_explicita(): void
    {
        Livewire::test(RevisionOcr::class)
            ->call('irA', 3)
            ->set('texto', 'Mi corrección local')
            ->call('guardar')
            ->call('tomarTextoVigente')
            ->assertSet('texto', ServicioDocumentosDoble::TEXTO_VIGENTE)
            ->assertSet('textoEnConflicto', null);
    }

    public function test_marcar_avanza_a_la_hoja_siguiente(): void
    {
        Livewire::test(RevisionOcr::class)
            ->call('irA', 1)
            ->call('marcar', 'CORRECTA')
            ->assertSet('indice', 2);
    }

    public function test_el_contador_refleja_el_progreso_real(): void
    {
        $componente = Livewire::test(RevisionOcr::class);

        // De las cinco hojas de ejemplo, tres ya están en estado terminal.
        $this->assertSame(3, $componente->get('revisadas'));

        $componente->call('irA', 1)->call('marcar', 'CORREGIDA');
        $this->assertSame(4, $componente->get('revisadas'));
    }

    public function test_una_transicion_no_permitida_muestra_el_motivo(): void
    {
        Livewire::test(RevisionOcr::class)
            ->call('irA', 0)          // PENDIENTE_OCR
            ->call('marcar', 'CORRECTA')
            ->assertSee('El OCR de esta hoja todavía está corriendo');
    }

    public function test_el_resumen_de_cierre_muestra_hojas_por_estado_y_por_tipo(): void
    {
        Livewire::test(CierreSesion::class)
            ->assertSee('Hojas del folder')
            ->assertSee('Correctas')
            ->assertSee('Corregidas')
            ->assertSee('Ilegibles')
            ->assertSee('hoja atencion')
            ->assertSee('receta');
    }

    public function test_una_hoja_ilegible_no_bloquea_el_cierre(): void
    {
        Livewire::test(CierreSesion::class)
            ->assertSee('No impiden cerrar el folder')
            ->call('cerrar')
            ->assertSet('estado', 'cerrada')
            ->assertSee('quedó cerrado');
    }

    public function test_reabrir_una_hoja_la_saca_de_la_cola_de_ilegibles(): void
    {
        Livewire::test(HojasIlegibles::class)
            ->assertSet('estado', 'exito')
            ->assertSee('Mamani Choque')
            ->call('reabrir', 8144)
            ->assertSet('estado', 'vacio')
            ->assertSee('La hoja volvió a revisión.')
            ->assertDontSee('Reabrir revisión');
    }

    /**
     * La respuesta de `GET /sesiones/{id}/hojas` sigue el contrato al pie:
     * envuelta en `datos` y con los campos declarados en cada hoja. `version`
     * es el que más importa, porque la corrección posterior lo exige.
     */
    public function test_las_hojas_de_sesion_traen_los_campos_del_contrato(): void
    {
        $respuesta = (new ServicioDocumentosDoble)->hojasDeSesion(77);

        $this->assertArrayHasKey('datos', $respuesta);

        foreach ($respuesta['datos'] as $hoja) {
            foreach (['id', 'orden', 'tipo', 'fechaDocumento', 'estadoRevision',
                'textoExtraido', 'textoCorregido', 'version', 'urlImagen'] as $campo) {
                $this->assertArrayHasKey($campo, $hoja, "falta {$campo}");
            }
            $this->assertIsInt($hoja['version']);
        }
    }

    public function test_el_doble_cubre_los_cinco_estados_y_el_conflicto(): void
    {
        $documentos = new ServicioDocumentosDoble;

        $estados = array_map(
            fn (array $h) => $h['estadoRevision'],
            $documentos->hojasDeSesion(77)['datos'],
        );

        foreach (['PENDIENTE_OCR', 'EN_REVISION', 'CORRECTA', 'CORREGIDA', 'ILEGIBLE'] as $estado) {
            $this->assertContains($estado, $estados);
        }

        try {
            $documentos->corregirTexto(ServicioDocumentosDoble::DOCUMENTO_CON_CONFLICTO, 'x', 1);
            $this->fail('Se esperaba VERSION_DESACTUALIZADA');
        } catch (ErrorDeAplicacion $e) {
            $this->assertSame('VERSION_DESACTUALIZADA', $e->getCodigo());
            $this->assertSame(ServicioDocumentosDoble::TEXTO_VIGENTE, $e->getDetalle()['textoActual']);
        }
    }
}
