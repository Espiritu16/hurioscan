<?php

namespace Tests\Feature\Frontend;

use App\Compartido\Dobles\Digitalizacion\ServicioDigitalizacionDoble;
use App\Compartido\Dobles\Documentos\ServicioDocumentosDoble;
use App\Compartido\Dobles\Pacientes\ServicioPacientesDoble;
use App\Compartido\Dobles\Usuarios\ServicioUsuariosDoble;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use App\Dominios\Pacientes\Componentes\BuscadorPacientes;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Toda acción visible conduce a algún sitio.
 *
 * `PaginaRealTest` verifica que un componente **puede** servirse, porque
 * registra su propia ruta para cada uno. Eso deja un hueco: un componente
 * alcanzable en aislamiento puede ser inalcanzable desde la aplicación, y sus
 * botones pueden salir inertes sin que nada falle. Fue el hallazgo QA-F-01:
 * «Registrar paciente nuevo» se veía habilitado y un clic no hacía nada.
 *
 * Esta prueba parte de las rutas **reales** de `routes/web.php` y comprueba
 * que cada control interactivo de cada página resuelve a algo: un enlace, una
 * acción del componente, un envío de formulario, o un control marcado
 * explícitamente como no disponible todavía.
 */
class AccionesConDestinoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->app->singleton(ServicioPacientes::class, ServicioPacientesDoble::class);
        $this->app->singleton(ServicioDigitalizacion::class, ServicioDigitalizacionDoble::class);
        $this->app->singleton(ServicioDocumentos::class, ServicioDocumentosDoble::class);
        $this->app->singleton(ServicioUsuarios::class, ServicioUsuariosDoble::class);
    }

    /**
     * Páginas de la aplicación por nombre de ruta real, con los parámetros
     * mínimos para servirlas. El catálogo de componentes queda fuera a
     * propósito: es una vitrina de desarrollo y sus botones son muestras, no
     * acciones.
     */
    public static function paginasDeLaAplicacion(): array
    {
        return [
            'acceder' => ['acceder', []],
            'pacientes' => ['pacientes', []],
            'pacientes.alta' => ['pacientes.alta', []],
            'pacientes.detalle' => ['pacientes.detalle', ['pacienteId' => 1]],
            // La apertura cuelga del paciente: su ruta lleva `pacienteId`,
            // que es la propiedad que espera el componente.
            'sesiones.apertura' => ['sesiones.apertura', ['pacienteId' => 1]],
            'sesiones.pendientes' => ['sesiones.pendientes', []],
            'sesiones.detalle' => ['sesiones.detalle', ['sesionId' => 77]],
            'sesiones.revision' => ['sesiones.revision', ['sesionId' => 77]],
            'sesiones.cierre' => ['sesiones.cierre', ['sesionId' => 77]],
            'ilegibles' => ['ilegibles', []],
            'buscar (sin término)' => ['buscar', []],
            // Con resultados aparecen controles que el estado inicial no
            // muestra; sin esto la página se probaría vacía.
            'buscar (con resultados)' => ['buscar', ['q' => 'hipertensión']],
            'pacientes.detalle (filtrado)' => ['pacientes.detalle', ['pacienteId' => 1, 'tipo' => 'receta']],
            'documentos.detalle' => ['documentos.detalle', ['documentoId' => 8142]],
            'documentos.detalle (ilegible)' => ['documentos.detalle', ['documentoId' => 8144]],
            'avance' => ['avance', []],
            'usuarios' => ['usuarios', []],
            'auditoria' => ['auditoria', []],
            'auditoria (filtrada)' => ['auditoria', ['entidad' => 'Paciente']],
        ];
    }

    #[DataProvider('paginasDeLaAplicacion')]
    public function test_ninguna_accion_de_la_pagina_queda_sin_destino(string $nombre, array $parametros): void
    {
        if (! Route::has($nombre)) {
            $this->markTestSkipped("la ruta {$nombre} todavía no está montada");
        }

        $html = $this->get(route($nombre, $parametros))->assertOk()->getContent();

        // Una página sin ningún control no probaría nada: se exige que haya
        // algo que revisar, para que el test no pase por vacío.
        $this->assertNotEmpty(
            $this->controlesInteractivos($html),
            "la página {$nombre} no expuso ningún control",
        );

        foreach ($this->controlesInteractivos($html) as $control) {
            $this->assertTrue(
                $this->conduceAAlgo($control),
                "control sin destino en la página {$nombre}: {$control}",
            );
        }
    }

    /**
     * El caso concreto de QA-F-01. Se prueba sobre el componente y no sobre la
     * página porque las acciones reservadas dependen del rol, y el rol lo
     * aporta la sesión: sin autenticación la página no las muestra, que es lo
     * correcto pero deja el cableado sin verificar.
     */
    public function test_la_busqueda_de_pacientes_conduce_al_alta_y_a_la_digitalizacion(): void
    {
        $html = Livewire::test(BuscadorPacientes::class, ['rol' => 'operador'])->html();

        foreach ($this->controlesInteractivos($html) as $control) {
            $this->assertTrue(
                $this->conduceAAlgo($control),
                "control sin destino en la búsqueda de pacientes: {$control}",
            );
        }

        // Los dos destinos que QA encontró inalcanzables.
        $this->assertStringContainsString('Registrar paciente nuevo', $html);
        $this->assertStringContainsString('Iniciar digitalización', $html);

        if (Route::has('pacientes.alta')) {
            $this->assertStringContainsString(route('pacientes.alta'), $html);
        }

        if (Route::has('sesiones.apertura')) {
            $this->assertStringContainsString(route('sesiones.apertura', ['pacienteId' => 1]), $html);
        }
    }

    /** El rol `consulta` no debe ver acciones de registro ni de captura. */
    public function test_el_rol_consulta_no_ve_acciones_reservadas(): void
    {
        $html = Livewire::test(BuscadorPacientes::class, ['rol' => 'consulta'])->html();

        $this->assertStringNotContainsString('Registrar paciente nuevo', $html);
        $this->assertStringNotContainsString('Iniciar digitalización', $html);
        $this->assertStringContainsString('Ver línea de tiempo', $html);
    }

    /** Sin rol —sin sesión— tampoco se ofrece nada reservado (RNF-013). */
    public function test_sin_rol_no_se_ofrece_ninguna_accion_reservada(): void
    {
        $html = Livewire::test(BuscadorPacientes::class)->html();

        $this->assertStringNotContainsString('Registrar paciente nuevo', $html);
        $this->assertStringNotContainsString('Iniciar digitalización', $html);
    }

    /** @return list<string> etiquetas de apertura de cada control */
    private function controlesInteractivos(string $html): array
    {
        preg_match_all('/<(?:a|button)\b[^>]*>/i', $html, $coincidencias);

        return array_values(array_filter(
            $coincidencias[0],
            // Los controles del andamiaje de Livewire no son acciones de la
            // interfaz; se reconocen por no tener ninguna marca propia.
            fn (string $control) => ! str_contains($control, 'wire:loading'),
        ));
    }

    /**
     * Un control conduce a algo si enlaza, dispara una acción del componente,
     * envía un formulario, o declara que su destino todavía no existe.
     */
    private function conduceAAlgo(string $control): bool
    {
        // Se buscan **atributos**, no subcadenas: `disabled` aparece también
        // dentro de las clases de Tailwind (`disabled:opacity-50`), y darlo
        // por bueno haría que cualquier botón pasara la prueba. Es el error
        // que tuvo la primera versión de este test.
        $atributos = [
            '/\shref=/',
            '/\swire:click[.=]/',
            '/\swire:submit[.=]/',
            '/\swire:change[.=]/',
            '/\stype="submit"/',
            '/\saria-disabled="true"/',
            '/\sdisabled(?=[\s>=])/',
            '/\sdata-alterna-menu(?=[\s>=])/',
        ];

        foreach ($atributos as $patron) {
            if (preg_match($patron, $control) === 1) {
                return true;
            }
        }

        return false;
    }
}
