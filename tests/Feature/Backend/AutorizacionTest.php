<?php

namespace Tests\Feature\Backend;

use App\Compartido\Dobles\Digitalizacion\ServicioDigitalizacionDoble;
use App\Compartido\Dobles\Documentos\ServicioDocumentosDoble;
use App\Compartido\Dobles\Pacientes\ServicioPacientesDoble;
use App\Compartido\Dobles\Usuarios\ServicioUsuariosDoble;
use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use App\Dominios\Digitalizacion\PoliticaDigitalizacion;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use App\Dominios\Usuarios\PoliticaUsuarios;
use App\Dominios\Usuarios\ServicioUsuariosEloquent;
use App\Dominios\Usuarios\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * UT-03 — control de acceso deny-by-default (RNF-013).
 *
 * Las páginas se sirven con los dobles de los dominios que todavía no tienen
 * backend, igual que hacen las pruebas de la línea frontend. No debilita nada:
 * lo que está bajo prueba es el middleware, que decide antes de que el
 * componente exista, y la identidad la aporta una sesión real sobre usuarios
 * reales de la base.
 */
class AutorizacionTest extends TestCase
{
    use RefreshDatabase;

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
     * Las rutas montadas, con un rol que la matriz permite y uno que no.
     *
     * `null` en el rol denegado significa que las tres filas existen y no hay a
     * quién denegar, no que la prueba se salte nada.
     *
     * Las cuatro pruebas que consumen este proveedor declaran los cuatro
     * parámetros aunque alguna no use todos: PHPUnit avisa cuando el proveedor
     * entrega más argumentos de los que el método acepta, y ese aviso hace
     * fallar la corrida.
     *
     * @return array<string, array{0: string, 1: array, 2: string, 3: string|null}>
     */
    public static function rutasProtegidas(): array
    {
        return [
            'pacientes' => ['pacientes', [], 'consulta', null],
            'pacientes.alta' => ['pacientes.alta', [], 'operador', 'consulta'],
            'pacientes.detalle' => ['pacientes.detalle', ['pacienteId' => 1], 'consulta', null],
            // `POST /sesiones` es del operador y solo del operador: la matriz
            // deniega la apertura al administrador, que no digitaliza.
            'sesiones.apertura' => ['sesiones.apertura', ['pacienteId' => 1], 'operador', 'administrador'],
            'sesiones.pendientes' => ['sesiones.pendientes', [], 'operador', 'consulta'],
            'sesiones.detalle' => ['sesiones.detalle', ['sesionId' => 77], 'operador', 'consulta'],
            'sesiones.revision' => ['sesiones.revision', ['sesionId' => 77], 'operador', 'consulta'],
            'sesiones.cierre' => ['sesiones.cierre', ['sesionId' => 77], 'operador', 'administrador'],
            'ilegibles' => ['ilegibles', [], 'operador', 'consulta'],
            'buscar' => ['buscar', [], 'consulta', null],
            'documentos.detalle' => ['documentos.detalle', ['documentoId' => 8142], 'consulta', null],
            'avance' => ['avance', [], 'operador', 'consulta'],
            'usuarios' => ['usuarios', [], 'administrador', 'operador'],
            'auditoria' => ['auditoria', [], 'administrador', 'operador'],
        ];
    }

    /** Prueba mínima 1 de RNF-013: sin autenticar. */
    #[DataProvider('rutasProtegidas')]
    public function test_sin_autenticar_la_ruta_rechaza(string $nombre, array $parametros, string $rolPermitido, ?string $rolDenegado): void
    {
        $this->assertTrue(Route::has($nombre), "la ruta {$nombre} no está montada");

        $this->get(route($nombre, $parametros))->assertRedirect(route('acceder'));

        // La misma petición esperando JSON conserva el código, que es la
        // identidad estable del error.
        $this->getJson(route($nombre, $parametros))
            ->assertStatus(401)
            ->assertJsonPath('codigo', 'NO_AUTENTICADO');
    }

    /** Prueba mínima 2 de RNF-013: con credencial válida. */
    #[DataProvider('rutasProtegidas')]
    public function test_con_credencial_valida_la_ruta_responde(string $nombre, array $parametros, string $rolPermitido, ?string $rolDenegado): void
    {
        $this->actingAs($this->usuarioConRol($rolPermitido))
            ->get(route($nombre, $parametros))
            ->assertOk();
    }

    /**
     * Prueba mínima 3 de RNF-013: con credencial que dejó de valer.
     *
     * El caso realista: la sesión sigue abierta y la cuenta se desactiva
     * después. Sin esta comprobación, desactivar a alguien no lo echaría del
     * sistema hasta que su sesión caducara sola.
     */
    #[DataProvider('rutasProtegidas')]
    public function test_con_credencial_que_dejo_de_valer_la_ruta_rechaza(string $nombre, array $parametros, string $rolPermitido, ?string $rolDenegado): void
    {
        $desactivado = $this->usuarioConRol($rolPermitido);
        $desactivado->update(['activo' => false]);

        $this->actingAs($desactivado)
            ->get(route($nombre, $parametros))
            ->assertRedirect(route('acceder'));

        // Y la sesión no sobrevive al rechazo: se cierra, no se deja abierta
        // para que el siguiente intento la reutilice.
        $this->assertGuest();
    }

    /**
     * La otra mitad del mismo caso: la sesión sigue abierta y el usuario ya no
     * existe.
     *
     * Va aparte y con sesión real —`POST /acceder`, no `actingAs`— porque
     * `actingAs` inyecta el objeto en el guard y nunca vuelve a consultarlo: un
     * usuario eliminado seguiría pareciendo válido y la prueba pasaría sin
     * haber ejercido nada. Lleva control positivo antes de eliminar, para que
     * un rechazo por «no había sesión» no se confunda con el rechazo que se
     * busca.
     */
    public function test_una_sesion_cuyo_usuario_fue_eliminado_rechaza(): void
    {
        $this->app->singleton(ServicioUsuarios::class, ServicioUsuariosEloquent::class);

        $usuario = $this->usuarioConRol('operador');

        $this->post('/acceder', ['email' => $usuario->email, 'password' => 'hurioscan']);
        $this->assertAuthenticatedAs($usuario);

        // Control positivo: releída desde la sesión, la credencial sigue siendo
        // válida y la página se sirve.
        $this->releerLaCredencial();
        $this->get(route('pacientes'))->assertOk();

        $usuario->delete();
        $this->releerLaCredencial();

        $this->get(route('pacientes'))->assertRedirect(route('acceder'));
        $this->assertGuest();
    }

    /** Un rol sin fila para esa operación recibe `NO_AUTORIZADO`, no un 404. */
    #[DataProvider('rutasProtegidas')]
    public function test_un_rol_sin_fila_recibe_no_autorizado(string $nombre, array $parametros, string $rolPermitido, ?string $rolDenegado): void
    {
        if ($rolDenegado === null) {
            // No es un caso saltado: la matriz concede esta operación a los tres
            // roles, así que se comprueba justamente eso, que ninguno queda
            // fuera. Si alguien recorta una fila, esto falla.
            foreach (['operador', 'consulta', 'administrador'] as $indice => $rol) {
                $this->actingAs($this->usuarioConRol($rol, "rol{$indice}@hurioscan.test"))
                    ->get(route($nombre, $parametros))
                    ->assertOk();
            }

            return;
        }

        $this->actingAs($this->usuarioConRol($rolDenegado))
            ->getJson(route($nombre, $parametros))
            ->assertStatus(403)
            ->assertJsonPath('codigo', 'NO_AUTORIZADO');
    }

    /** El formulario de acceso es la única pantalla que alcanza el anónimo. */
    public function test_el_formulario_de_acceso_es_publico(): void
    {
        $this->get(route('acceder'))->assertOk();
    }

    /**
     * El criterio de cierre de UT-03: una operación sin regla declarada rechaza.
     *
     * La ruta se fabrica aquí en vez de buscar una del sistema que esté sin
     * declarar: la prueba tiene que valer también cuando todas las rutas reales
     * estén bien declaradas, que es el estado deseable.
     */
    public function test_una_operacion_sin_fila_en_la_matriz_rechaza(): void
    {
        Route::get('/_sin-fila', fn () => 'nunca debería llegar aquí')
            ->middleware(['web', 'autorizar:GET /operacion-que-nadie-declaro']);

        $this->actingAs($this->usuarioConRol('administrador'))
            ->getJson('/_sin-fila')
            ->assertStatus(403)
            ->assertJsonPath('codigo', 'NO_AUTORIZADO');
    }

    /** Y una ruta que ni siquiera dice qué operación sirve, tampoco pasa. */
    public function test_una_ruta_que_no_declara_su_operacion_rechaza(): void
    {
        Route::get('/_sin-declarar', fn () => 'nunca debería llegar aquí')
            ->middleware(['web', 'autorizar']);

        $this->actingAs($this->usuarioConRol('administrador'))
            ->getJson('/_sin-declarar')
            ->assertStatus(403)
            ->assertJsonPath('codigo', 'NO_AUTORIZADO');
    }

    /** La política de dominio aplica la misma regla desde el servicio. */
    public function test_la_politica_de_dominio_rechaza_sin_fila_y_sin_sesion(): void
    {
        $politica = new PoliticaUsuarios;

        // Sin sesión: `NO_AUTENTICADO`, nunca «tratar como anónimo».
        try {
            $politica->exigir('GET /usuarios');
            $this->fail('autorizó sin sesión');
        } catch (ErrorDeAplicacion $e) {
            $this->assertSame('NO_AUTENTICADO', $e->getCodigo());
        }

        // Con sesión pero sin fila para ese rol: `NO_AUTORIZADO`.
        $this->actingAs($this->usuarioConRol('operador'));

        try {
            $politica->exigir('GET /usuarios');
            $this->fail('autorizó a un operador la administración de usuarios');
        } catch (ErrorDeAplicacion $e) {
            $this->assertSame('NO_AUTORIZADO', $e->getCodigo());
        }

        // Y una operación que no es del dominio no se confunde con una
        // denegación: es una llamada mal escrita y se nota como tal.
        $this->expectException(LogicException::class);
        $politica->exigir('GET /pacientes');
    }

    /**
     * Una condición de alcance no se puede omitir.
     *
     * Es el modo de fallo que este diseño existe para impedir: el servicio
     * llama a la política, la política ve que la fila exige «solo las propias»,
     * y si nadie le dice si se cumple **no** la da por buena.
     */
    public function test_una_condicion_de_alcance_omitida_no_se_da_por_cumplida(): void
    {
        $politica = new PoliticaDigitalizacion;

        $this->actingAs($this->usuarioConRol('operador'));

        try {
            $politica->exigir('GET /sesiones/pendientes');
            $this->fail('la política dio por cumplida una condición que nadie evaluó');
        } catch (LogicException $e) {
            $this->assertStringContainsString('condición de alcance', $e->getMessage());
        }

        // Declarada y no cumplida, rechaza; declarada y cumplida, pasa.
        try {
            $politica->exigir('GET /sesiones/pendientes', false);
            $this->fail('autorizó fuera de alcance');
        } catch (ErrorDeAplicacion $e) {
            $this->assertSame('NO_AUTORIZADO', $e->getCodigo());
        }

        $politica->exigir('GET /sesiones/pendientes', true);
        $this->addToAssertionCount(1);
    }

    private function usuarioConRol(string $rol, string $email = 'actor@hurioscan.test'): Usuario
    {
        return Usuario::factory()->conRol($rol)->create(['email' => $email]);
    }

    /**
     * Obliga a resolver la credencial otra vez desde la sesión.
     *
     * Entre dos peticiones de una misma prueba el guard conserva en memoria el
     * usuario que ya resolvió, así que sin esto la segunda petición no volvería
     * a consultar la base y no se estaría probando nada.
     */
    private function releerLaCredencial(): void
    {
        Auth::forgetGuards();
    }
}
