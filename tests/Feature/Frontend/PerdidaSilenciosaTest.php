<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Digitalizacion\ServicioDigitalizacionDoble;
use App\Dominios\Digitalizacion\Componentes\CapturaHojas;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ninguna hoja desaparece sin que la interfaz lo diga.
 *
 * Una hoja rechazada está explicada y no es una pérdida. Lo que se persigue
 * aquí es lo que se descarta antes de que la aplicación lo vea —por ejemplo al
 * superar `max_file_uploads` de PHP, que hoy corta en veinte archivos por
 * petición sin emitir ningún error—. Sin aviso, el contador simplemente
 * mostraría menos hojas y el operador no tendría contra qué notarlo.
 */
class PerdidaSilenciosaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->app->singleton(ServicioDigitalizacion::class, ServicioDigitalizacionDoble::class);
    }

    /** @return list<UploadedFile> */
    private function hojas(int $cuantas): array
    {
        return array_map(
            fn (int $i) => UploadedFile::fake()->create("hoja-{$i}.jpg", 300, 'image/jpeg'),
            range(1, $cuantas),
        );
    }

    public function test_avisa_cuando_llegan_menos_hojas_de_las_elegidas(): void
    {
        // El navegador informó 30; el servidor solo entregó 20.
        $componente = Livewire::test(CapturaHojas::class)
            ->set('seleccionadas', 30)
            ->call('agregar', $this->hojas(20));

        $componente->assertCount('hojas', 20);
        $componente->assertSee('Elegiste 30 hojas y solo se procesaron 20');
        $componente->assertSee('descartó 10');
        // Es un aviso que el lector de pantalla anuncia, no un texto suelto.
        $componente->assertSee('role="alert"', false);
    }

    public function test_no_avisa_cuando_llegan_todas(): void
    {
        Livewire::test(CapturaHojas::class)
            ->set('seleccionadas', 5)
            ->call('agregar', $this->hojas(5))
            ->assertSet('avisoPerdida', null)
            ->assertCount('hojas', 5);
    }

    /** Una hoja rechazada está explicada: no cuenta como desaparecida. */
    public function test_un_rechazo_con_motivo_no_se_cuenta_como_perdida(): void
    {
        $componente = Livewire::test(CapturaHojas::class)
            ->set('seleccionadas', 3)
            ->call('agregar', [
                UploadedFile::fake()->create('a.jpg', 300, 'image/jpeg'),
                UploadedFile::fake()->create('notas.txt', 10, 'text/plain'),
                UploadedFile::fake()->create('enorme.jpg', 18 * 1024, 'image/jpeg'),
            ]);

        $componente->assertCount('hojas', 1);
        $componente->assertCount('rechazos', 2);
        // Las tres están explicadas: una capturada y dos con su motivo.
        $componente->assertSet('avisoPerdida', null);
    }

    /** Sin dato del navegador no se inventa un aviso. */
    public function test_sin_cuenta_del_navegador_no_avisa(): void
    {
        Livewire::test(CapturaHojas::class)
            ->call('agregar', $this->hojas(4))
            ->assertSet('avisoPerdida', null)
            ->assertCount('hojas', 4);
    }

    /** La cuenta vale para su lote: no arrastra un aviso al siguiente. */
    public function test_la_cuenta_no_se_arrastra_al_lote_siguiente(): void
    {
        $componente = Livewire::test(CapturaHojas::class)
            ->set('seleccionadas', 10)
            ->call('agregar', $this->hojas(4));

        $componente->assertSee('Elegiste 10 hojas');
        $componente->assertSet('seleccionadas', 0);

        // Segundo lote completo: el aviso anterior desaparece.
        $componente->set('seleccionadas', 2)->call('agregar', $this->hojas(2));
        $componente->assertSet('avisoPerdida', null);
    }

    public function test_la_vista_avisa_mientras_el_lote_sube(): void
    {
        Livewire::test(CapturaHojas::class)
            ->assertSee('Subiendo hojas…', false)
            ->assertSee('wire:loading', false);
    }
}
