<?php

namespace Tests\Unit;

use App\Compartido\Configuracion\LimitesDelEntorno;
use PHPUnit\Framework\TestCase;

/**
 * Deuda heredada de la cadena de frontend: la aplicación verifica sus propios
 * límites de subida al arrancar y falla de forma visible si están por debajo
 * del límite del producto.
 *
 * La comprobación se prueba como función pura, con valores dados, y no leyendo
 * el `php.ini` de quien corra la suite: una prueba que dependiera del entorno
 * pasaría o fallaría por motivos ajenos al código.
 */
class LimitesDelEntornoTest extends TestCase
{
    /** Los valores por defecto de una máquina cualquiera: el defecto QA-F-03. */
    public function test_los_valores_por_defecto_de_php_se_denuncian(): void
    {
        $incumplimientos = LimitesDelEntorno::incumplimientos([
            'upload_max_filesize' => '2M',
            'post_max_size' => '8M',
        ]);

        $this->assertCount(2, $incumplimientos);
        $this->assertStringContainsString('upload_max_filesize', $incumplimientos[0]);
        $this->assertStringContainsString('post_max_size', $incumplimientos[1]);
    }

    /** Los del repositorio (`scripts/php/hurioscan.ini`) pasan. */
    public function test_los_valores_del_proyecto_cumplen(): void
    {
        $this->assertSame([], LimitesDelEntorno::incumplimientos([
            'upload_max_filesize' => '50M',
            'post_max_size' => '60M',
        ]));
    }

    /**
     * Igualar el límite del producto **no** alcanza.
     *
     * Es el criterio de Arquitectura y el caso que más fácil se cuela: con
     * exactamente 15 MB, una hoja de 15 MB y un byte vuelve a descartar la
     * petición entera antes de llegar al dominio, que es justo lo que el
     * dominio existe para rechazar hoja por hoja.
     */
    public function test_igualar_el_limite_del_producto_no_alcanza(): void
    {
        $this->assertNotSame([], LimitesDelEntorno::incumplimientos([
            'upload_max_filesize' => '15M',
            'post_max_size' => '15M',
        ]));
    }

    /** `0` significa «sin tope», no un límite bajísimo. */
    public function test_sin_tope_no_es_un_incumplimiento(): void
    {
        $this->assertSame([], LimitesDelEntorno::incumplimientos([
            'upload_max_filesize' => '0',
            'post_max_size' => '0',
        ]));
    }

    /** Una directiva ausente cuenta como incumplimiento, no como permitida. */
    public function test_una_directiva_ausente_no_se_da_por_buena(): void
    {
        $this->assertCount(2, LimitesDelEntorno::incumplimientos([]));
    }

    /** El texto dice qué pasa y qué hacer, no solo que algo está mal. */
    public function test_la_explicacion_dice_como_arrancar_bien(): void
    {
        $explicacion = LimitesDelEntorno::explicacion(
            LimitesDelEntorno::incumplimientos(['upload_max_filesize' => '2M', 'post_max_size' => '8M'])
        );

        $this->assertStringContainsString('scripts/servir-desarrollo.sh', $explicacion);
        $this->assertStringContainsString('2M', $explicacion);
    }
}
