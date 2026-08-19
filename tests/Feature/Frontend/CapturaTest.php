<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Digitalizacion\ServicioDigitalizacionDoble;
use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Componentes\AperturaSesion;
use App\Dominios\Digitalizacion\Componentes\CapturaHojas;
use App\Dominios\Digitalizacion\Componentes\SesionesPendientes;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use Livewire\Livewire;
use Tests\TestCase;

class CapturaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->app->singleton(ServicioDigitalizacion::class, ServicioDigitalizacionDoble::class);
    }

    /** Archivo de ejemplo con el mime y el tamaño que el doble inspecciona. */
    private function archivo(string $nombre, string $mime, int $tamano): array
    {
        return ['nombre' => $nombre, 'mime' => $mime, 'tamano' => $tamano];
    }

    public function test_una_sesion_ya_abierta_ofrece_retomarla_con_enlace(): void
    {
        Livewire::test(AperturaSesion::class, ['pacienteId' => 4])
            ->call('abrir')
            ->assertSet('estado', 'ya_abierta')
            ->assertSet('sesionExistenteId', 77)
            // Con salida, no un error sin retorno.
            ->assertSee('Retomar la sesión 77')
            ->assertDontSee('role="alert"', false);
    }

    public function test_un_paciente_sin_sesion_abre_una_nueva(): void
    {
        Livewire::test(AperturaSesion::class, ['pacienteId' => 1])
            ->call('abrir')
            ->assertSet('estado', 'abierta')
            ->assertSee('Continúa con la captura');
    }

    public function test_una_hoja_rechazada_no_borra_las_ya_capturadas(): void
    {
        $componente = Livewire::test(CapturaHojas::class)
            ->call('agregar', [$this->archivo('hoja-01.jpg', 'image/jpeg', 900_000)])
            ->call('agregar', [$this->archivo('hoja-02.jpg', 'image/jpeg', 850_000)]);

        $componente->assertCount('hojas', 2);

        // Una hoja con formato no admitido.
        $componente->call('agregar', [$this->archivo('notas.txt', 'text/plain', 1_000)]);

        $componente
            ->assertCount('hojas', 2)          // las anteriores siguen ahí
            ->assertCount('rechazos', 1)
            ->assertSee('notas.txt')
            ->assertSee('Formato no admitido');
    }

    public function test_una_hoja_demasiado_grande_explica_el_motivo(): void
    {
        Livewire::test(CapturaHojas::class)
            ->call('agregar', [$this->archivo('folio.jpg', 'image/jpeg', 20 * 1024 * 1024)])
            ->assertCount('hojas', 0)
            ->assertSee('Pesa más de 15 MB');
    }

    public function test_las_tres_vias_de_captura_estan_disponibles(): void
    {
        Livewire::test(CapturaHojas::class)
            ->assertSee('Tomar foto')
            ->assertSee('Elegir de la galería')
            ->assertSee('Subir archivos')
            // Las tres desembocan en el mismo camino de captura.
            ->assertSee('wire:model="archivosCamara"', false)
            ->assertSee('wire:model="archivosGaleria"', false)
            ->assertSee('wire:model="archivos"', false)
            // La cámara pide la trasera, que es con la que se fotografía papel.
            ->assertSee('capture="environment"', false);
    }

    public function test_el_tipo_y_la_fecha_se_heredan_de_la_hoja_anterior(): void
    {
        $componente = Livewire::test(CapturaHojas::class)
            ->set('tipo', 'receta')
            ->set('fechaDocumento', '2021-06-09')
            ->call('agregar', [$this->archivo('a.jpg', 'image/jpeg', 100)])
            ->call('agregar', [$this->archivo('b.jpg', 'image/jpeg', 100)]);

        foreach ($componente->get('hojas') as $hoja) {
            $this->assertSame('receta', $hoja['tipo']);
            $this->assertSame('2021-06-09', $hoja['fechaDocumento']);
        }
    }

    public function test_quitar_una_hoja_reordena_las_siguientes(): void
    {
        $componente = Livewire::test(CapturaHojas::class)
            ->call('agregar', [
                $this->archivo('a.jpg', 'image/jpeg', 100),
                $this->archivo('b.jpg', 'image/jpeg', 100),
                $this->archivo('c.jpg', 'image/jpeg', 100),
            ]);

        $segunda = $componente->get('hojas')[1]['id'];
        $componente->call('quitar', $segunda);

        $ordenes = array_column($componente->get('hojas'), 'orden');
        $this->assertSame([1, 2], $ordenes);
    }

    public function test_la_captura_vacia_muestra_estado_vacio_y_no_deja_continuar(): void
    {
        Livewire::test(CapturaHojas::class)
            ->assertSee('Todavía no hay hojas capturadas')
            ->assertDontSee('Continuar a revisión');
    }

    public function test_enviar_a_revision_sin_hojas_explica_el_motivo(): void
    {
        Livewire::test(CapturaHojas::class)
            ->call('enviarARevision')
            ->assertSet('enviadaARevision', false)
            ->assertSee('Captura al menos una hoja');
    }

    public function test_las_sesiones_pendientes_dicen_donde_se_retoma_cada_una(): void
    {
        Livewire::test(SesionesPendientes::class)
            ->assertSet('estado', 'exito')
            ->assertSee('Zárate Pinto')
            // ABIERTA vuelve a captura; EN_REVISION, a revisión.
            ->assertSee('Retomar en captura')
            ->assertSee('Retomar en revisión');
    }

    public function test_el_doble_cubre_los_desenlaces_declarados(): void
    {
        $digitalizacion = new ServicioDigitalizacionDoble;

        try {
            $digitalizacion->abrirSesion(4);
            $this->fail('Se esperaba SESION_YA_ABIERTA');
        } catch (ErrorDeAplicacion $e) {
            $this->assertSame('SESION_YA_ABIERTA', $e->getCodigo());
            $this->assertSame(77, $e->getDetalle()['sesionExistenteId']);
        }

        foreach ([
            [$this->archivo('x.txt', 'text/plain', 10), 'HOJA_FORMATO_NO_SOPORTADO'],
            [$this->archivo('x.jpg', 'image/jpeg', 20 * 1024 * 1024), 'HOJA_DEMASIADO_GRANDE'],
        ] as [$archivo, $codigo]) {
            try {
                $digitalizacion->agregarHoja(101, $archivo, 'hoja_atencion');
                $this->fail("Se esperaba {$codigo}");
            } catch (ErrorDeAplicacion $e) {
                $this->assertSame($codigo, $e->getCodigo());
            }
        }
    }
}
