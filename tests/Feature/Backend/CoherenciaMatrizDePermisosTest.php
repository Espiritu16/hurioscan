<?php

namespace Tests\Feature\Backend;

use App\Compartido\Autorizacion\MatrizDePermisos;
use ReflectionClass;
use Tests\TestCase;

/**
 * La matriz de permisos en código contra `docs/requisitos/actores-permisos.md`.
 *
 * Existe porque **nada comparaba las dos**: `GET /sesiones/{id}/hojas` faltaba
 * entera en el código y la condición de `PATCH /usuarios/{id}` se había perdido
 * en la transcripción, y ninguna prueba se puso en rojo por ello. Con el
 * rechazo por defecto de RNF-013, una fila que falta no es una laguna: es una
 * denegación silenciosa para todos los roles.
 *
 * ## Qué se compara, y qué se decidió que es formato
 *
 * El documento es una tabla Markdown en prosa y la matriz una constante PHP con
 * etiquetas cortas. Normalizar hasta que todo coincida convertiría esta prueba
 * en un verde permanente, así que la normalización es **deliberadamente
 * mínima** y se aplica igual a los dos lados:
 *
 * - se quitan las comillas invertidas de Markdown (``ABIERTA`` → `ABIERTA`),
 *   que son marcado del documento y no texto;
 * - se colapsan los espacios consecutivos.
 *
 * **No** se baja a minúsculas, **no** se quitan acentos ni puntuación, **no**
 * se recorta. Una condición que difiera en cualquier otra cosa es una
 * divergencia y la prueba falla.
 *
 * Las pocas condiciones que el documento y la matriz redactan distinto a
 * propósito se declaran **una por una** en `EQUIVALENCIAS`, con los dos textos
 * exactos y la clave de la fila concreta. Una equivalencia no puede alcanzar a
 * otra fila, no puede excusar la aparición o desaparición de una condición
 * —sus dos lados son cadenas, nunca `null`— y no puede quedarse de adorno: la
 * prueba exige además que todas las declaradas correspondan a una divergencia
 * real y viva.
 */
class CoherenciaMatrizDePermisosTest extends TestCase
{
    private const DOCUMENTO = 'docs/requisitos/actores-permisos.md';

    /**
     * Sentinela con el que ambos lados representan el actor `Anónimo`: la fila
     * `(ninguno)` del documento y `MatrizDePermisos::PUBLICA` del código.
     */
    private const SIN_CREDENCIAL = '(sin credencial)';

    /**
     * Divergencias de redacción aprobadas: `operación|rol` => [texto del
     * documento, texto de la matriz], ambos ya normalizados.
     *
     * Cada entrada es una afirmación revisable de que esas dos frases dicen lo
     * mismo. Cambiar cualquiera de los dos lados rompe la coincidencia exacta y
     * la prueba falla, que es justo lo que debe pasar.
     */
    private const EQUIVALENCIAS = [
        'GET /sesiones/pendientes|operador' => [
            'solo las propias (sesion.operador_id == actor.id)',
            'sesion.operador_id == actor.id',
        ],
        'GET /sesiones/{id}|operador' => [
            'solo las propias (sesion.operador_id == actor.id)',
            'sesion.operador_id == actor.id',
        ],
        'GET /sesiones/{id}/hojas|operador' => [
            'solo las propias (sesion.operador_id == actor.id)',
            'sesion.operador_id == actor.id',
        ],
        'POST /sesiones/{id}/enviar-a-revision|operador' => [
            'sesión propia, estado ABIERTA, con al menos una hoja',
            'sesión propia, ABIERTA, con al menos una hoja',
        ],
        'POST /sesiones/{id}/cerrar|operador' => [
            'sesión propia, estado EN_REVISION, sin hojas en PENDIENTE_OCR ni EN_REVISION',
            'sesión propia, EN_REVISION, sin hojas sin revisar',
        ],
    ];

    /**
     * Antes de comparar nada: que el parser encuentre la tabla entera.
     *
     * Un parser que no encuentra ninguna fila haría pasar las comparaciones
     * comparando dos conjuntos vacíos — cobertura ficticia con forma de verde.
     * Por eso aquí no se afirma solo «encontró algo»: se afirma que **toda**
     * línea de tabla del documento se convirtió en una fila, salvo la cabecera
     * y su separador.
     */
    public function test_el_parseo_del_documento_encuentra_la_tabla_entera(): void
    {
        $lectura = $this->leerDocumento();

        $this->assertSame(
            2,
            $lectura['omitidas'],
            'se omitieron líneas de tabla además de la cabecera y su separador: el parser está perdiendo filas',
        );
        $this->assertCount(
            $lectura['lineasDeTabla'] - 2,
            $lectura['filas'],
            'no toda línea de la tabla se convirtió en fila',
        );
        $this->assertGreaterThanOrEqual(
            50,
            count($lectura['filas']),
            'el parser encontró muy pocas filas: la tabla cambió de forma y esta prueba dejó de mirarla',
        );
        $this->assertGreaterThanOrEqual(
            25,
            count(array_unique(array_column($lectura['filas'], 'operacion'))),
            'muy pocas operaciones distintas: la columna «Recurso/Operación» dejó de parsearse',
        );

        // La tabla tiene filas de los dos signos. Si solo se vieran las «Sí»,
        // la comparación no podría detectar un permiso de más en el código.
        $this->assertNotEmpty(array_filter($lectura['filas'], fn (array $f) => $f['permitido']));
        $this->assertNotEmpty(array_filter($lectura['filas'], fn (array $f) => ! $f['permitido']));
    }

    public function test_toda_combinacion_permitida_del_documento_existe_en_la_matriz(): void
    {
        $documento = $this->clavesDelDocumento();
        $matriz = $this->clavesDeLaMatriz();

        $this->assertSame(
            [],
            array_values(array_diff($documento, $matriz)),
            'el documento permite combinaciones que la matriz no declara; con el rechazo por defecto de RNF-013 quedan denegadas para todos',
        );
    }

    public function test_toda_fila_de_la_matriz_esta_permitida_en_el_documento(): void
    {
        $documento = $this->clavesDelDocumento();
        $matriz = $this->clavesDeLaMatriz();

        $this->assertSame(
            [],
            array_values(array_diff($matriz, $documento)),
            'la matriz concede permisos que el documento no permite (o que declara «No»)',
        );
    }

    public function test_las_condiciones_de_alcance_coinciden(): void
    {
        $documento = $this->condicionesDelDocumento();
        $matriz = $this->condicionesDeLaMatriz();

        $comunes = array_intersect_key($documento, $matriz);
        $this->assertGreaterThanOrEqual(
            30,
            count($comunes),
            'demasiado pocas combinaciones comparadas: algo dejó de verse',
        );

        $equivalenciasUsadas = [];

        foreach ($comunes as $clave => $condicionDocumentada) {
            $condicionEnCodigo = $matriz[$clave];

            if ($condicionDocumentada === $condicionEnCodigo) {
                continue;
            }

            $this->assertArrayHasKey(
                $clave,
                self::EQUIVALENCIAS,
                "«{$clave}»: el documento dice «".var_export($condicionDocumentada, true).
                '» y la matriz «'.var_export($condicionEnCodigo, true).
                '». Si son lo mismo dicho de otra forma, declárelo en EQUIVALENCIAS; si no, corrija la matriz.',
            );
            $this->assertSame(
                [$condicionDocumentada, $condicionEnCodigo],
                self::EQUIVALENCIAS[$clave],
                "«{$clave}»: la equivalencia declarada ya no describe estos dos textos",
            );

            $equivalenciasUsadas[] = $clave;
        }

        sort($equivalenciasUsadas);
        $declaradas = array_keys(self::EQUIVALENCIAS);
        sort($declaradas);

        $this->assertSame(
            $declaradas,
            $equivalenciasUsadas,
            'hay equivalencias declaradas que ya no corresponden a ninguna divergencia real: una equivalencia sobrante es permiso para divergir sin que nadie lo note',
        );
    }

    // --- Documento -----------------------------------------------------

    /**
     * @return array{filas: list<array{operacion: string, rol: string, condicion: ?string, permitido: bool}>, lineasDeTabla: int, omitidas: int}
     */
    private function leerDocumento(): array
    {
        $ruta = base_path(self::DOCUMENTO);
        $this->assertFileExists($ruta);

        $lineas = array_values(preg_grep('/^\s*\|/', file($ruta, FILE_IGNORE_NEW_LINES)));
        $this->assertNotEmpty($lineas, 'el documento no contiene ninguna tabla');

        $filas = [];
        $omitidas = 0;

        foreach ($lineas as $linea) {
            $celdas = array_map('trim', explode('|', trim($linea)));
            array_shift($celdas);
            array_pop($celdas);

            // Cabecera («Permitido») y separador («---») son las dos únicas
            // líneas de tabla que no son filas de datos.
            if (count($celdas) !== 7 || ! in_array($celdas[5], ['Sí', 'No'], true)) {
                $omitidas++;

                continue;
            }

            $this->assertMatchesRegularExpression(
                '/^`[^`]+`/',
                $celdas[2],
                "la columna «Recurso/Operación» no empieza por la operación entre comillas invertidas: «{$celdas[2]}»",
            );
            preg_match('/^`([^`]+)`/', $celdas[2], $coincidencia);

            $rol = $celdas[1] === '(ninguno)' ? self::SIN_CREDENCIAL : $celdas[1];

            $filas[] = [
                'operacion' => $this->normalizar($coincidencia[1]),
                'rol' => $rol,
                'condicion' => $celdas[4] === '—' ? null : $this->normalizar($celdas[4]),
                'permitido' => $celdas[5] === 'Sí',
            ];
        }

        return ['filas' => $filas, 'lineasDeTabla' => count($lineas), 'omitidas' => $omitidas];
    }

    /** @return list<string> claves `operación|rol` que el documento permite */
    private function clavesDelDocumento(): array
    {
        $claves = array_keys($this->condicionesDelDocumento());
        sort($claves);

        $this->assertGreaterThanOrEqual(
            30,
            count($claves),
            'el documento aportó muy pocas combinaciones permitidas: comparar así no verificaría nada',
        );

        return $claves;
    }

    /** @return array<string, ?string> `operación|rol` => condición documentada */
    private function condicionesDelDocumento(): array
    {
        $condiciones = [];

        foreach ($this->leerDocumento()['filas'] as $fila) {
            if (! $fila['permitido']) {
                continue;
            }

            $condiciones[$fila['operacion'].'|'.$fila['rol']] = $fila['condicion'];
        }

        return $condiciones;
    }

    // --- Matriz en código ----------------------------------------------

    /** @return list<string> */
    private function clavesDeLaMatriz(): array
    {
        $claves = array_keys($this->condicionesDeLaMatriz());
        sort($claves);

        $this->assertGreaterThanOrEqual(
            30,
            count($claves),
            'la matriz aportó muy pocas combinaciones: se leyó mal la constante',
        );

        return $claves;
    }

    /**
     * Se lee la constante por reflexión, no por la API pública, porque la API
     * responde preguntas («¿permite este rol?») y aquí hace falta el
     * **inventario completo** de lo declarado: un rol escrito mal en la matriz
     * no aparecería preguntando por los roles que el documento conoce.
     *
     * @return array<string, ?string> `operación|rol` => condición en código
     */
    private function condicionesDeLaMatriz(): array
    {
        $matriz = (new ReflectionClass(MatrizDePermisos::class))->getConstant('MATRIZ');

        $this->assertIsArray($matriz, 'no se pudo leer la constante MATRIZ');
        $this->assertGreaterThanOrEqual(4, count($matriz), 'la matriz declara menos dominios de los que el proyecto tiene');

        $condiciones = [];

        foreach ($matriz as $operaciones) {
            foreach ($operaciones as $operacion => $valor) {
                if ($valor === MatrizDePermisos::PUBLICA) {
                    $condiciones[$operacion.'|'.self::SIN_CREDENCIAL] = null;

                    continue;
                }

                foreach ($valor as $rol => $condicion) {
                    $condiciones[$operacion.'|'.$rol] = $condicion === null ? null : $this->normalizar($condicion);
                }
            }
        }

        return $condiciones;
    }

    /**
     * Normalización mínima, idéntica en los dos lados: quitar el marcado de
     * Markdown y colapsar espacios. Nada más — ver el encabezado de la clase.
     */
    private function normalizar(string $texto): string
    {
        return trim(preg_replace('/\s+/u', ' ', str_replace('`', '', $texto)));
    }
}
