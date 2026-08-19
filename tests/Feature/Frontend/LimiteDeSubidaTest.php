<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Digitalizacion\ServicioDigitalizacionDoble;
use App\Dominios\Digitalizacion\Componentes\CapturaHojas;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El límite de tamaño se aplica hoja por hoja, no al lote (QA-F-03).
 *
 * Antes de AUT-03 no existía `config/livewire.php`, así que regía el default
 * del framework (`max:12288`, 12 MB). Esa validación corre **antes** que el
 * componente y descarta la petición de subida entera: una sola hoja pesada
 * hacía desaparecer todo el lote, sin motivo visible y con un mensaje en
 * inglés. Estas pruebas fijan el comportamiento que exige F03-UT-03.
 */
class LimiteDeSubidaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->app->singleton(ServicioDigitalizacion::class, ServicioDigitalizacionDoble::class);
    }

    private function archivo(string $nombre, int $megas): UploadedFile
    {
        return UploadedFile::fake()->create($nombre, $megas * 1024, 'image/jpeg');
    }

    /** 13 MB está por debajo del límite del producto: es una hoja válida. */
    public function test_una_hoja_de_13_mb_se_captura(): void
    {
        Livewire::test(CapturaHojas::class)
            ->set('archivos', [$this->archivo('folio-13.jpg', 13)])
            ->assertCount('hojas', 1)
            ->assertCount('rechazos', 0);
    }

    /** El caso exacto que QA midió: el lote no puede perderse. */
    public function test_una_hoja_de_16_mb_se_rechaza_y_las_demas_se_conservan(): void
    {
        $componente = Livewire::test(CapturaHojas::class)
            ->set('archivos', [
                $this->archivo('folio-16.jpg', 16),
                $this->archivo('folio-bueno.jpg', 1),
            ]);

        $componente->assertCount('hojas', 1);      // la válida sobrevive
        $componente->assertCount('rechazos', 1);
        $componente->assertSee('folio-16.jpg');
        // El mensaje es el del producto, en español, no el del framework.
        $componente->assertSee('Pesa más de 15 MB');
        $componente->assertDontSee('may not be greater than');
    }

    /** Un lote mixto conserva todas las válidas y explica cada rechazo. */
    public function test_un_lote_mixto_conserva_las_validas_y_explica_los_rechazos(): void
    {
        $componente = Livewire::test(CapturaHojas::class)
            ->set('archivos', [
                $this->archivo('a.jpg', 1),
                $this->archivo('enorme.jpg', 18),
                $this->archivo('b.jpg', 2),
                UploadedFile::fake()->create('notas.txt', 10, 'text/plain'),
                $this->archivo('c.jpg', 13),
            ]);

        // Cuatro válidas —incluida la de 13 MB— y dos rechazos con su motivo.
        $componente->assertCount('hojas', 3);
        $componente->assertCount('rechazos', 2);
        $componente->assertSee('Pesa más de 15 MB');
        $componente->assertSee('Formato no admitido');
    }

    /**
     * La configuración publicada no debe volver a imponer un tope de tamaño:
     * si alguien restaura `max:...`, el lote vuelve a perderse entero.
     */
    public function test_la_configuracion_no_impone_un_tope_de_tamano(): void
    {
        $reglas = config('livewire.temporary_file_upload.rules');

        $this->assertIsArray($reglas, 'sin config/livewire.php rige el default de 12 MB');

        foreach ($reglas as $regla) {
            $this->assertStringNotContainsString(
                'max:',
                (string) $regla,
                'el tope de tamaño se aplica por hoja en el servicio, no en la subida',
            );
        }
    }
}
