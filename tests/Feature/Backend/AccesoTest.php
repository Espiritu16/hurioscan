<?php

namespace Tests\Feature\Backend;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use App\Dominios\Usuarios\ServicioUsuariosEloquent;
use App\Dominios\Usuarios\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * UT-02 — acceso y salida del sistema (`docs/contratos/usuarios.md`, RF-011).
 *
 * Corre contra la implementación real: el doble no se activa (`DOBLE_USUARIOS`
 * en `false`) y `AppServiceProvider` liga la interfaz a `ServicioUsuariosEloquent`.
 */
class AccesoTest extends TestCase
{
    use RefreshDatabase;

    private const CLAVE = 'clave-de-prueba-77';

    private function usuario(array $atributos = []): Usuario
    {
        return Usuario::factory()->create($atributos + [
            'email' => 'operador@hurioscan.test',
            'password' => self::CLAVE,
            'rol' => 'operador',
        ]);
    }

    /** El servicio bajo prueba es el real, no el doble. */
    public function test_la_interfaz_de_usuarios_resuelve_a_la_implementacion_real(): void
    {
        $this->assertFalse(config('dobles.usuarios'));
        $this->assertInstanceOf(
            ServicioUsuariosEloquent::class,
            $this->app->make(ServicioUsuarios::class),
        );
    }

    public function test_una_credencial_valida_crea_la_sesion_y_lleva_al_panel_del_rol(): void
    {
        $usuario = $this->usuario();

        $this->post('/acceder', ['email' => $usuario->email, 'password' => self::CLAVE])
            ->assertRedirect(route('avance'));

        $this->assertAuthenticatedAs($usuario);
    }

    /** El rol `consulta` no alcanza el panel de avance: entra por pacientes. */
    public function test_el_rol_consulta_entra_por_la_busqueda_de_pacientes(): void
    {
        $usuario = $this->usuario(['rol' => 'consulta', 'email' => 'consulta@hurioscan.test']);

        $this->post('/acceder', ['email' => $usuario->email, 'password' => self::CLAVE])
            ->assertRedirect(route('pacientes'));
    }

    /** El correo se compara sin distinguir mayúsculas y con los bordes recortados. */
    public function test_el_correo_se_normaliza_antes_de_comparar(): void
    {
        $usuario = $this->usuario();

        $this->post('/acceder', ['email' => '  OPERADOR@HuriosCan.TEST  ', 'password' => self::CLAVE])
            ->assertRedirect(route('avance'));

        $this->assertAuthenticatedAs($usuario);
    }

    /** La contraseña no se recorta: un espacio puede ser parte de ella. */
    public function test_la_contrasena_no_se_recorta(): void
    {
        $this->usuario(['password' => ' clave con espacios ']);

        $this->post('/acceder', ['email' => 'operador@hurioscan.test', 'password' => 'clave con espacios'])
            ->assertStatus(401);

        $this->assertGuest();
    }

    /**
     * Los tres modos de fallo comparten mensaje **exacto**.
     *
     * No se comparan contra un literal escrito a mano: se comparan entre sí, de
     * modo que si alguien personaliza uno de los tres la prueba falla aunque el
     * texto nuevo también parezca razonable. Eso es lo que protege el contrato:
     * distinguirlos revelaría qué correos están registrados.
     */
    public function test_credencial_invalida_usuario_inexistente_e_inactivo_dan_el_mismo_no_autenticado(): void
    {
        $this->usuario();
        $this->usuario(['email' => 'inactivo@hurioscan.test', 'activo' => false]);
        $this->usuario(['email' => 'borrado@hurioscan.test'])->delete();

        $mensajes = [];

        foreach ([
            'contraseña incorrecta' => ['operador@hurioscan.test', 'otra-clave-cualquiera'],
            'usuario inexistente' => ['nadie@hurioscan.test', self::CLAVE],
            'usuario inactivo' => ['inactivo@hurioscan.test', self::CLAVE],
            'usuario eliminado' => ['borrado@hurioscan.test', self::CLAVE],
        ] as $caso => [$email, $password]) {
            $mensajes[$caso] = $this->mensajeDeFallo($email, $password);
            $this->assertFalse(Auth::check(), "«{$caso}» no debería haber autenticado");
        }

        $this->assertCount(1, array_unique($mensajes), 'los mensajes de NO_AUTENTICADO difieren: '.json_encode($mensajes));
    }

    /** La misma distinción, vista desde la ruta: código y status del contrato. */
    public function test_la_ruta_devuelve_no_autenticado_con_su_status(): void
    {
        $this->usuario();

        $this->postJson('/acceder', ['email' => 'operador@hurioscan.test', 'password' => 'otra-clave'])
            ->assertStatus(401)
            ->assertJsonPath('codigo', 'NO_AUTENTICADO');
    }

    public function test_un_campo_invalido_devuelve_validacion_entrada_con_su_detalle(): void
    {
        $respuesta = $this->postJson('/acceder', ['email' => 'no-es-un-correo', 'password' => 'corta'])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDACION_ENTRADA');

        $campos = array_column($respuesta->json('detalle.campos'), 'regla', 'campo');

        $this->assertSame('formato', $campos['email']);
        $this->assertSame('longitud_minima', $campos['password']);
    }

    /**
     * `recordar` solo admite booleanos reales: la cadena `"false"` no se
     * interpreta como verdadera por ser no vacía, que es lo que haría un cast
     * directo.
     */
    public function test_recordar_rechaza_un_valor_que_no_sea_booleano(): void
    {
        $this->usuario();

        $this->postJson('/acceder', [
            'email' => 'operador@hurioscan.test',
            'password' => self::CLAVE,
            'recordar' => 'quizá',
        ])->assertStatus(422)->assertJsonPath('codigo', 'VALIDACION_ENTRADA');

        $this->assertGuest();
    }

    public function test_recordar_verdadero_deja_la_credencial_persistente(): void
    {
        $this->usuario();

        $this->post('/acceder', [
            'email' => 'operador@hurioscan.test',
            'password' => self::CLAVE,
            'recordar' => true,
        ])->assertCookie(Auth::getRecallerName());
    }

    /** Fijación de sesión: el identificador previo deja de valer al entrar. */
    public function test_el_identificador_de_sesion_cambia_al_autenticar(): void
    {
        $this->usuario();

        $this->get(route('acceder'));
        $antes = session()->getId();

        $this->post('/acceder', ['email' => 'operador@hurioscan.test', 'password' => self::CLAVE]);

        $this->assertNotSame($antes, session()->getId());
    }

    public function test_salir_cierra_la_sesion_y_devuelve_al_formulario(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->post('/salir')
            ->assertRedirect(route('acceder'));

        $this->assertGuest();
    }

    /** Sin sesión activa la operación rechaza; no se degrada a anónimo. */
    public function test_salir_sin_sesion_rechaza(): void
    {
        $this->post('/salir')->assertRedirect(route('acceder'));

        $this->postJson('/salir')
            ->assertStatus(401)
            ->assertJsonPath('codigo', 'NO_AUTENTICADO');
    }

    /**
     * La contraseña no aparece en ningún log (RNF-014, criterio de UT-02).
     *
     * La comprobación empieza por escribir una marca propia en el archivo y
     * verificar que llega: sin eso, «la contraseña no está en el log» sería
     * cierto simplemente porque el log está en otro sitio, y la guarda no
     * podría fallar nunca.
     */
    public function test_la_contrasena_no_aparece_en_ningun_log(): void
    {
        $ruta = storage_path('logs/prueba-acceso-'.uniqid().'.log');

        config([
            'logging.default' => 'prueba',
            'logging.channels.prueba' => ['driver' => 'single', 'path' => $ruta, 'level' => 'debug'],
        ]);

        Log::debug('marca-de-canario');
        $this->assertStringContainsString('marca-de-canario', file_get_contents($ruta), 'el log de prueba no es el destino real');

        $this->usuario();

        // Los cuatro caminos que tocan la contraseña: éxito, credencial
        // inválida, validación fallida y cierre de sesión.
        $this->post('/acceder', ['email' => 'operador@hurioscan.test', 'password' => self::CLAVE]);
        $this->post('/acceder', ['email' => 'operador@hurioscan.test', 'password' => self::CLAVE.'-mal']);
        $this->post('/acceder', ['email' => 'no-es-correo', 'password' => self::CLAVE]);
        $this->post('/salir');

        $contenido = file_get_contents($ruta);
        unlink($ruta);

        $this->assertStringNotContainsString(self::CLAVE, $contenido);
        // Y tampoco vuelve al navegador como input recordado.
        $this->assertStringNotContainsString(self::CLAVE, json_encode(session()->all()));
    }

    /** Mensaje del `NO_AUTENTICADO` que lanza el servicio, sin pasar por HTTP. */
    private function mensajeDeFallo(string $email, string $password): string
    {
        try {
            $this->app->make(ServicioUsuarios::class)->autenticar($email, $password);
        } catch (ErrorDeAplicacion $e) {
            $this->assertSame('NO_AUTENTICADO', $e->getCodigo());

            return $e->getMessage();
        }

        $this->fail("autenticó con «{$email}», que no debía");
    }
}
