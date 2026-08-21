<?php

namespace Tests\Feature\Backend;

use App\Compartido\Dobles\Usuarios\ServicioUsuariosDoble;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use App\Dominios\Usuarios\ServicioUsuariosEloquent;
use App\Dominios\Usuarios\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;
use SensitiveParameterValue;
use Tests\TestCase;
use Throwable;

/**
 * `#[\SensitiveParameter]` en la contraseña — `docs/contratos/servicios-aplicacion.md`
 * § Parámetros sensibles en las firmas, RNF-014.
 *
 * ## Por qué esta prueba apaga una directiva antes de mirar
 *
 * El entorno lleva `zend.exception_ignore_args=1` (`scripts/php/hurioscan.ini`,
 * puesto por DevOps para QA-B01-02). Esa directiva vacía los argumentos de
 * **todos** los frames, así que con ella activa la contraseña no aparece en el
 * trace **aunque el atributo no esté**: una prueba corrida bajo ella no puede
 * fallar nunca, y sería el quinto caso de cobertura ficticia del proyecto.
 *
 * Por eso la directiva se apaga aquí de forma explícita (es `INI_ALL`), se
 * comprueba que el apagado surtió efecto, y se restaura al terminar. La
 * comprobación no es cosmética: si algún día la directiva dejara de poder
 * apagarse en runtime, esta prueba se pondría en rojo en vez de volverse muda.
 *
 * La directiva **no se retira** del `.ini`: Arquitectura decidió conservar las
 * dos capas y explica por qué. Apagarla dentro de esta prueba es lo contrario
 * de retirarla — es mirar debajo de la manta para comprobar que la capa de
 * abajo también hace su trabajo.
 */
class ParametrosSensiblesTest extends TestCase
{
    use RefreshDatabase;

    /** Por debajo del recorte de 15 caracteres del trace, como fija `AccesoTest`. */
    private const CLAVE = 'clave-77';

    private const CORREO = 'operador@hurioscan.test';

    private ?string $directivaOriginal = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directivaOriginal = ini_get('zend.exception_ignore_args');
        ini_set('zend.exception_ignore_args', '0');

        $this->assertSame(
            '0',
            ini_get('zend.exception_ignore_args'),
            'no se pudo apagar zend.exception_ignore_args: bajo esa directiva esta prueba no puede fallar y no verificaría nada',
        );
    }

    protected function tearDown(): void
    {
        if ($this->directivaOriginal !== null) {
            ini_set('zend.exception_ignore_args', $this->directivaOriginal);
        }

        parent::tearDown();
    }

    /**
     * Las cuatro declaraciones de la firma llevan el atributo.
     *
     * La interfaz nunca aparece en un trace, así que el control de
     * comportamiento no puede vigilarla; esta comprobación estática sí. Y
     * `validarCredenciales` es privada pero recibe la misma contraseña: una
     * excepción dentro del validador la escribiría igual.
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    public static function declaracionesDeLaFirma(): array
    {
        return [
            'interfaz' => [ServicioUsuarios::class, 'autenticar', 'password'],
            'implementación real' => [ServicioUsuariosEloquent::class, 'autenticar', 'password'],
            'doble de desarrollo' => [ServicioUsuariosDoble::class, 'autenticar', 'password'],
            'validador interno' => [ServicioUsuariosEloquent::class, 'validarCredenciales', 'password'],
        ];
    }

    #[DataProvider('declaracionesDeLaFirma')]
    public function test_la_contrasena_esta_declarada_sensible_en_la_firma(string $clase, string $metodo, string $parametro): void
    {
        $reflexion = new ReflectionMethod($clase, $metodo);
        $encontrado = null;

        foreach ($reflexion->getParameters() as $candidato) {
            if ($candidato->getName() === $parametro) {
                $encontrado = $candidato;
            }
        }

        $this->assertNotNull($encontrado, "{$clase}::{$metodo}() ya no recibe «{$parametro}»");
        $this->assertNotEmpty(
            $encontrado->getAttributes(\SensitiveParameter::class),
            "{$clase}::{$metodo}(\${$parametro}) perdió #[\\SensitiveParameter]: su valor volvería al trace",
        );

        // El correo NO se marca: el criterio del contrato dice que marcar de
        // más degrada el diagnóstico sin proteger nada.
        if ($metodo === 'autenticar') {
            $email = (new ReflectionClass($clase))->getMethod('autenticar')->getParameters()[0];
            $this->assertSame('email', $email->getName());
            $this->assertEmpty(
                $email->getAttributes(\SensitiveParameter::class),
                'el correo quedó marcado como sensible: se pierde el argumento que sirve para diagnosticar',
            );
        }
    }

    /**
     * El caso de QA-B01-02 tal cual se reprodujo: una excepción **inesperada**
     * dentro de `autenticar()` sobre la implementación real.
     */
    public function test_una_excepcion_inesperada_en_la_implementacion_real_no_lleva_la_contrasena_al_trace(): void
    {
        Usuario::factory()->create([
            'email' => self::CORREO,
            'password' => self::CLAVE,
            'rol' => 'operador',
        ]);

        // Lo que QA forzó con un `DROP TABLE usuarios`: el fallo no es del
        // dominio, así que nadie lo esperaba ni lo filtró.
        Schema::drop('usuarios');

        $this->comprobarElFrameDeAutenticar(
            $this->app->make(ServicioUsuarios::class),
            'QueryException dentro de la implementación real',
        );
    }

    /**
     * El doble corre en local y testing y recibe la misma contraseña, así que
     * su trace filtra igual. `ErrorDeAplicacion` basta para provocarlo: la
     * excepción nace dentro de `autenticar()`.
     */
    public function test_el_doble_de_desarrollo_tampoco_lleva_la_contrasena_al_trace(): void
    {
        $this->comprobarElFrameDeAutenticar(
            new ServicioUsuariosDoble,
            'ErrorDeAplicacion dentro del doble',
        );
    }

    /**
     * Provoca la excepción, encuentra el frame de `autenticar()` y comprueba
     * las tres cosas que el contrato promete: que el frame conserva sus
     * argumentos (si no, la manta está puesta y no se verificaría nada), que el
     * correo sigue ahí para diagnosticar, y que la contraseña llegó redactada.
     */
    private function comprobarElFrameDeAutenticar(ServicioUsuarios $servicio, string $caso): void
    {
        $excepcion = null;

        try {
            $servicio->autenticar(self::CORREO, self::CLAVE.'-mal', true);
        } catch (Throwable $e) {
            $excepcion = $e;
        }

        $this->assertNotNull($excepcion, "{$caso}: no se produjo ninguna excepción, no hay trace que mirar");

        $frame = null;
        foreach ($excepcion->getTrace() as $candidato) {
            if (($candidato['function'] ?? null) === 'autenticar') {
                $frame = $candidato;
                break;
            }
        }

        $this->assertNotNull($frame, "{$caso}: el trace no contiene el frame de autenticar()");
        $this->assertArrayHasKey(
            'args',
            $frame,
            "{$caso}: el frame no conserva sus argumentos — zend.exception_ignore_args sigue activa y esta comprobación sería ficticia",
        );

        $this->assertSame(
            self::CORREO,
            $frame['args'][0],
            "{$caso}: el correo debía seguir en el trace; sin él el atributo estaría costando diagnóstico de más",
        );
        $this->assertInstanceOf(
            SensitiveParameterValue::class,
            $frame['args'][1],
            "{$caso}: la contraseña viaja en claro en el trace — falta #[\\SensitiveParameter]",
        );

        $this->assertStringNotContainsString(
            self::CLAVE,
            $this->traceComoTexto($excepcion),
            "{$caso}: la contraseña aparece en el volcado del trace",
        );
        $this->assertStringNotContainsString(
            self::CLAVE,
            $excepcion->getTraceAsString(),
            "{$caso}: la contraseña aparece en la representación en texto del trace",
        );
    }

    /** Volcado del trace crudo, que es lo que consume un logger estructurado. */
    private function traceComoTexto(Throwable $excepcion): string
    {
        return print_r(array_map(
            fn (array $frame) => array_map(
                fn (mixed $valor) => is_scalar($valor) || $valor === null ? $valor : get_debug_type($valor),
                $frame['args'] ?? [],
            ),
            $excepcion->getTrace(),
        ), true);
    }
}
