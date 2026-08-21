<?php

namespace Tests\Feature\Backend;

use App\Dominios\Usuarios\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * RNF-005 — todo instante se almacena y se lee como el mismo momento UTC.
 *
 * La forma de verificarlo la fija el propio RNF: crear un registro con la zona
 * de sesión alterada y comprobar que el instante recuperado es idéntico al
 * esperado en UTC.
 *
 * «Zona de sesión» se toma en los dos sentidos que existen, porque el instante
 * se desplaza por cualquiera de los dos: la del motor, que decide cómo
 * interpreta PostgreSQL una fecha sin zona al escribirla, y la de PHP, que
 * decide cómo la interpreta Carbon al leerla.
 */
class FundacionUtcTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Una hora deliberadamente incómoda: en hora de Perú (UTC-5) este instante
     * cae en el **día anterior**, así que un desplazamiento de zona cambiaría
     * también la fecha y no solo la hora.
     */
    private const INSTANTE_UTC = '2026-08-20 03:30:00';

    private string $zonaOriginalDePhp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zonaOriginalDePhp = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->zonaOriginalDePhp);
        parent::tearDown();
    }

    /**
     * La zona interna es UTC y no la decide el entorno.
     *
     * Es la guarda contra el modo de fallo real: alguien pone
     * `APP_TIMEZONE=America/Lima` para «arreglar» las fechas que se muestran y
     * a partir de ahí todo lo que se escribe queda desplazado cinco horas, sin
     * que nada falle. Por eso `config/app.php` fija UTC sin leer el entorno, y
     * la zona de Perú vive aparte, como decisión de presentación.
     */
    public function test_la_zona_interna_de_la_aplicacion_es_utc(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('UTC', date_default_timezone_get());
        $this->assertNotSame(config('app.timezone'), config('app.zona_visualizacion'));
    }

    /** El criterio de cierre de UT-01, literal. */
    public function test_un_instante_guardado_con_la_zona_de_sesion_alterada_se_recupera_identico_en_utc(): void
    {
        $instante = CarbonImmutable::parse(self::INSTANTE_UTC, 'UTC');

        $this->alterarZonaDeSesion('America/Lima');

        $usuario = Usuario::factory()->create([
            'email' => 'zona@hurioscan.test',
            'created_at' => $instante,
        ]);

        $recuperado = Usuario::query()->findOrFail($usuario->id);

        $this->assertTrue(
            $instante->equalTo($recuperado->created_at),
            'el instante recuperado no es el mismo momento que el guardado',
        );
        $this->assertSame(
            '2026-08-20T03:30:00+00:00',
            $recuperado->created_at->utc()->toIso8601String(),
        );

        // Y lo que quedó escrito es ese mismo momento, no la hora local de la
        // sesión que lo escribió. Sin esta comprobación la prueba pasaría igual
        // con una escritura desplazada, porque la lectura desharía el mismo
        // desplazamiento y nadie notaría nada.
        $almacenado = $this->instanteAlmacenado($usuario->id);

        $this->assertMatchesRegularExpression(
            '/[+-]\d{2}:?\d{0,2}$/',
            $almacenado,
            'el instante se guardó sin desplazamiento: su lectura depende de la zona de quien lo lea',
        );
        $this->assertTrue(
            $instante->equalTo(CarbonImmutable::parse($almacenado)),
            "el instante almacenado ({$almacenado}) no es el momento que se guardó",
        );
    }

    /**
     * Altera las dos zonas de sesión y **comprueba que la alteración
     * ocurrió**. Sin esa comprobación la prueba pasaría igual si la alteración
     * no tuviera efecto, y estaría verificando el caso fácil creyendo verificar
     * el difícil.
     */
    private function alterarZonaDeSesion(string $zona): void
    {
        date_default_timezone_set($zona);
        $this->assertSame($zona, date_default_timezone_get());

        match ($this->motor()) {
            'pgsql' => $this->alterarZonaDePostgres($zona),
            // SQLite no tiene zona de sesión porque no guarda zona en absoluto:
            // no hay nada que alterar ahí. Es la base de retroalimentación
            // rápida de la suite; el motor del proyecto es PostgreSQL y sobre
            // él corre la suite Feature en CI (AGENTS.md), que es donde esta
            // prueba ejerce las dos zonas. La de PHP ya quedó alterada arriba
            // para ambos motores.
            'sqlite' => null,
            // Un motor nuevo tiene que fallar aquí, no colarse sin alterar
            // nada: eso convertiría esta prueba en una alarma apagada.
            default => $this->fail("motor sin zona de sesión contemplada: {$this->motor()}"),
        };
    }

    private function alterarZonaDePostgres(string $zona): void
    {
        DB::statement('SET TIME ZONE '.DB::getPdo()->quote($zona));

        $this->assertSame(
            $zona,
            DB::selectOne('SHOW TIME ZONE')->TimeZone,
            'la zona de sesión de PostgreSQL no cambió',
        );
    }

    /** El valor tal como quedó en la fila, sin pasar por el modelo. */
    private function instanteAlmacenado(int $usuarioId): string
    {
        return (string) DB::table('usuarios')->where('id', $usuarioId)->value('created_at');
    }

    private function motor(): string
    {
        return DB::connection()->getDriverName();
    }
}
