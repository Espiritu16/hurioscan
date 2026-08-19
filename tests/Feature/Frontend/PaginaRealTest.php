<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Digitalizacion\ServicioDigitalizacionDoble;
use App\Compartido\Dobles\Documentos\ServicioDocumentosDoble;
use App\Compartido\Dobles\Pacientes\ServicioPacientesDoble;
use App\Compartido\Dobles\Usuarios\ServicioUsuariosDoble;
use App\Dominios\Digitalizacion\Componentes\AperturaSesion;
use App\Dominios\Digitalizacion\Componentes\CapturaHojas;
use App\Dominios\Digitalizacion\Componentes\CierreSesion;
use App\Dominios\Digitalizacion\Componentes\PanelAvance;
use App\Dominios\Digitalizacion\Componentes\SesionesPendientes;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use App\Dominios\Documentos\Componentes\BusquedaContenido;
use App\Dominios\Documentos\Componentes\HojasIlegibles;
use App\Dominios\Documentos\Componentes\RevisionOcr;
use App\Dominios\Documentos\Componentes\VisorDocumento;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use App\Dominios\Pacientes\Componentes\BuscadorPacientes;
use App\Dominios\Pacientes\Componentes\FormularioPaciente;
use App\Dominios\Pacientes\Componentes\LineaDeTiempo;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;
use App\Dominios\Usuarios\Componentes\AdministracionUsuarios;
use App\Dominios\Usuarios\Componentes\ConsultaAuditoria;
use App\Dominios\Usuarios\Componentes\FormularioAcceso;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cada componente servido como **página real**, no con `Livewire::test()`.
 *
 * Por qué existe: `Livewire::test()` renderiza el componente sin el layout de
 * página, así que un layout ausente o roto pasa desapercibido y solo estalla
 * al montar la ruta. Es exactamente lo que ocurrió — faltaba
 * `resources/views/layouts/app.blade.php` y los 84 tests seguían en verde.
 *
 * Las rutas se registran aquí porque `routes/web.php` pertenece a la línea
 * backend; cuando existan las reales, esta prueba las cubrirá igual.
 */
class PaginaRealTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // El CI corre los tests antes de compilar los assets, así que el
        // manifiesto de Vite no existe todavía. Lo que aquí se verifica es que
        // la página se sirve con su layout, no que los assets estén
        // compilados.
        $this->withoutVite();
        $this->app->singleton(ServicioPacientes::class, ServicioPacientesDoble::class);
        $this->app->singleton(ServicioDigitalizacion::class, ServicioDigitalizacionDoble::class);
        $this->app->singleton(ServicioDocumentos::class, ServicioDocumentosDoble::class);
        $this->app->singleton(ServicioUsuarios::class, ServicioUsuariosDoble::class);
    }

    /** Nombre de la página => componente que la sirve. */
    public static function componentesDePagina(): array
    {
        return [
            'acceder' => ['acceder', FormularioAcceso::class],
            'pacientes' => ['pacientes', BuscadorPacientes::class],
            'pacientes-alta' => ['pacientes-alta', FormularioPaciente::class],
            'pacientes-detalle' => ['pacientes-detalle', LineaDeTiempo::class],
            'sesiones-apertura' => ['sesiones-apertura', AperturaSesion::class],
            'sesiones-detalle' => ['sesiones-detalle', CapturaHojas::class],
            'sesiones-pendientes' => ['sesiones-pendientes', SesionesPendientes::class],
            'sesiones-revision' => ['sesiones-revision', RevisionOcr::class],
            'sesiones-cierre' => ['sesiones-cierre', CierreSesion::class],
            'ilegibles' => ['ilegibles', HojasIlegibles::class],
            'buscar' => ['buscar', BusquedaContenido::class],
            'documentos-detalle' => ['documentos-detalle', VisorDocumento::class],
            'avance' => ['avance', PanelAvance::class],
            'usuarios' => ['usuarios', AdministracionUsuarios::class],
            'auditoria' => ['auditoria', ConsultaAuditoria::class],
        ];
    }

    #[DataProvider('componentesDePagina')]
    public function test_el_componente_responde_como_pagina_completa(string $nombre, string $componente): void
    {
        Route::get('/_pagina/'.$nombre, $componente);

        $respuesta = $this->get('/_pagina/'.$nombre);

        $respuesta->assertOk();
        // El layout de página se aplicó de verdad: documento HTML completo.
        $respuesta->assertSee('<!DOCTYPE html>', false);
        $respuesta->assertSee('HuriosCan');
    }

    public function test_sin_sesion_autenticada_el_menu_no_ofrece_ninguna_opcion(): void
    {
        // El layout de página toma el rol de la sesión. Sin autenticar no hay
        // rol, y el menú no muestra nada: oculta de más, nunca de menos.
        // La degradación a `aria-disabled` cuando sí hay rol pero falta la
        // ruta está cubierta en ComponentesEstructuraTest.
        Route::get('/_pagina/solo-avance', PanelAvance::class);

        $respuesta = $this->get('/_pagina/solo-avance')->assertOk();

        $respuesta->assertDontSee('Panel de avance');
        $respuesta->assertDontSee('Usuarios');
        $respuesta->assertDontSee('Auditoría');
        // La página en sí sí se sirve.
        $respuesta->assertSee('Folders digitalizados');
    }
}
