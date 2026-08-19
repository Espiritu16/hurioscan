<?php

namespace App\Compartido\Dobles\Documentos;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;

/**
 * Doble de desarrollo del dominio Documentos (F05-UT-01, F06-UT-01).
 *
 * Cubre los cinco estados de revisión y el conflicto de versión. Uno de los
 * documentos trae un payload XSS dentro del texto extraído a propósito: es lo
 * que permite verificar que la vista lo muestra escapado y nunca lo ejecuta
 * (RNF-012).
 */
class ServicioDocumentosDoble implements ServicioDocumentos
{
    /** Documento cuyo `version` no coincide, para ver el conflicto. */
    public const DOCUMENTO_CON_CONFLICTO = 8143;

    /** Texto vigente que la vista debe mostrar ante un conflicto. */
    public const TEXTO_VIGENTE = 'Indicación por hipertensión: Enalapril 10 mg, 60 tabletas. Corregido por otra sesión.';

    /** Payload guardado tal cual: escapar al guardar corrompería el documento. */
    public const PAYLOAD_XSS = '<script>alert("xss")</script> Paciente refiere cefalea occipital.';

    private array $documentos;

    public function __construct()
    {
        $this->documentos = [
            8140 => $this->doc(8140, 'hoja_atencion', '2021-06-09', 'PENDIENTE_OCR', null),
            8141 => $this->doc(8141, 'hoja_atencion', '2021-06-09', 'EN_REVISION', self::PAYLOAD_XSS),
            8142 => $this->doc(8142, 'hoja_atencion', '2021-06-09', 'CORRECTA', 'DIAGNÓSTICO: Hipertensión arterial esencial I10. TRATAMIENTO: Enalapril 10 mg c/12 h por 30 días.'),
            8143 => $this->doc(8143, 'receta', '2021-06-09', 'CORREGIDA', 'Indicación por hipertensión: Enalapril 10 mg, 60 tabletas.'),
            8144 => $this->doc(8144, 'otro', null, 'ILEGIBLE', null),
        ];
    }

    private function doc(int $id, string $tipo, ?string $fecha, string $estado, ?string $texto): array
    {
        return [
            'id' => $id,
            'paciente' => ['id' => 1, 'numeroHistoria' => '04-118-2297', 'apellidos' => 'Mamani Choque', 'nombres' => 'Rosa Elena'],
            'tipo' => $tipo,
            'fechaDocumento' => $fecha,
            'estadoRevision' => $estado,
            'textoExtraido' => $texto,
            'textoCorregido' => null,
            'version' => 1,
            'motorOcr' => $texto === null ? null : 'nulo',
            'urlImagen' => '/build/assets/hoja-ejemplo.png',
            'digitalizadoPor' => ['id' => 1, 'nombre' => 'R. Quispe Tito'],
            'digitalizadoEn' => '2026-08-14T10:20:00Z',
            'sesionId' => 77,
            'orden' => $id - 8139,
        ];
    }

    public function ver(int $documentoId): array
    {
        return $this->documentos[$documentoId]
            ?? throw new ErrorDeAplicacion('RECURSO_NO_ENCONTRADO', 'El documento no existe.');
    }

    /**
     * Hojas de una sesión, en su orden de captura. Sin paginar: una sesión es
     * un folder y se revisa completa. La respuesta va envuelta en `datos`,
     * como declara el contrato, y cada hoja lleva su `version` porque la
     * corrección posterior con `PATCH /documentos/{id}/texto` la exige.
     */
    public function hojasDeSesion(int $sesionId): array
    {
        return ['datos' => array_values($this->documentos)];
    }

    public function corregirTexto(int $documentoId, string $texto, int $version): array
    {
        $documento = $this->ver($documentoId);

        if ($documentoId === self::DOCUMENTO_CON_CONFLICTO && $version === $documento['version']) {
            // Otra sesión escribió antes: la vista muestra el texto vigente y
            // deja decidir; nunca reenvía en silencio con la versión nueva.
            throw new ErrorDeAplicacion(
                'VERSION_DESACTUALIZADA',
                'Alguien más corrigió esta hoja mientras la editabas.',
                ['textoActual' => self::TEXTO_VIGENTE, 'version' => $documento['version'] + 1],
            );
        }

        $this->documentos[$documentoId]['textoCorregido'] = $texto;
        $this->documentos[$documentoId]['version'] = $version + 1;
        $this->documentos[$documentoId]['estadoRevision'] = 'EN_REVISION';

        return [
            'id' => $documentoId,
            'textoCorregido' => $texto,
            'version' => $version + 1,
            'estadoRevision' => 'EN_REVISION',
            'actualizadoEn' => '2026-08-19T13:00:00Z',
        ];
    }

    public function marcar(int $documentoId, string $resultado): array
    {
        if (! in_array($resultado, ['CORRECTA', 'CORREGIDA', 'ILEGIBLE'], true)) {
            throw new ErrorDeAplicacion('ESTADO_DOCUMENTO_INVALIDO', 'Ese resultado no existe.');
        }

        $documento = $this->ver($documentoId);

        // Desde un estado terminal no se vuelve a marcar: hay que reabrir.
        if (in_array($documento['estadoRevision'], ['CORRECTA', 'CORREGIDA', 'ILEGIBLE'], true)) {
            throw new ErrorDeAplicacion(
                'TRANSICION_DOCUMENTO_INVALIDA',
                'Esta hoja ya está revisada. Reábrela si necesitas cambiarla.',
            );
        }

        if ($documento['estadoRevision'] === 'PENDIENTE_OCR') {
            throw new ErrorDeAplicacion(
                'TRANSICION_DOCUMENTO_INVALIDA',
                'El OCR de esta hoja todavía está corriendo.',
            );
        }

        $this->documentos[$documentoId]['estadoRevision'] = $resultado;

        return [
            'id' => $documentoId,
            'estadoRevision' => $resultado,
            'revisadoEn' => '2026-08-19T13:05:00Z',
            'revisadoPor' => 1,
            'version' => $documento['version'],
        ];
    }

    public function reabrirRevision(int $documentoId): array
    {
        $documento = $this->ver($documentoId);

        if ($documento['estadoRevision'] === 'EN_REVISION') {
            throw new ErrorDeAplicacion('TRANSICION_DOCUMENTO_INVALIDA', 'Esta hoja ya está en revisión.');
        }

        $this->documentos[$documentoId]['estadoRevision'] = 'EN_REVISION';

        return ['id' => $documentoId, 'estadoRevision' => 'EN_REVISION', 'revisadoEn' => null, 'version' => $documento['version']];
    }

    public function reintentarOcr(int $documentoId): array
    {
        return ['id' => $documentoId, 'estadoRevision' => 'PENDIENTE_OCR', 'encoladoEn' => '2026-08-19T13:10:00Z'];
    }

    public function buscar(string $termino, array $filtros = [], int $pagina = 1): array
    {
        $termino = trim($termino);

        if ($termino === '') {
            throw new ErrorDeAplicacion('BUSQUEDA_TERMINO_VACIO', 'Escribe algo que buscar.');
        }

        $corpus = [
            ['documentoId' => 8142, 'tipo' => 'hoja_atencion', 'fechaDocumento' => '2021-06-09', 'texto' => 'DIAGNÓSTICO: Hipertensión arterial esencial I10 · Enalapril 10 mg c/12 h', 'relevancia' => 0.91],
            ['documentoId' => 8143, 'tipo' => 'receta', 'fechaDocumento' => '2021-06-09', 'texto' => 'Indicación por hipertensión: Enalapril 10 mg, 60 tabletas', 'relevancia' => 0.84],
            ['documentoId' => 8098, 'tipo' => 'laboratorio', 'fechaDocumento' => '2019-02-14', 'texto' => 'Perfil lipídico solicitado por hipertensión en control anual', 'relevancia' => 0.72],
            // Con un payload en el texto, para verificar el resaltado escapado.
            ['documentoId' => 8141, 'tipo' => 'hoja_atencion', 'fechaDocumento' => '2021-06-09', 'texto' => self::PAYLOAD_XSS.' Control de hipertensión.', 'relevancia' => 0.55],
        ];

        $encontrados = array_values(array_filter(
            $corpus,
            fn (array $r) => str_contains(mb_strtolower($r['texto']), mb_strtolower($termino)),
        ));

        if (isset($filtros['tipo']) && $filtros['tipo'] !== '') {
            $encontrados = array_values(array_filter($encontrados, fn (array $r) => $r['tipo'] === $filtros['tipo']));
        }

        $paciente = ['id' => 1, 'numeroHistoria' => '04-118-2297', 'apellidos' => 'Mamani Choque', 'nombres' => 'Rosa Elena'];

        return [
            'datos' => array_map(fn (array $r) => [
                'documentoId' => $r['documentoId'],
                'paciente' => $paciente,
                'tipo' => $r['tipo'],
                'fechaDocumento' => $r['fechaDocumento'],
                // El fragmento viaja como texto plano: el resaltado lo aplica
                // la vista después de escapar (RNF-012).
                'fragmento' => $r['texto'],
                'relevancia' => $r['relevancia'],
            ], $encontrados),
            'meta' => ['pagina' => $pagina, 'porPagina' => 20, 'total' => count($encontrados), 'totalPaginas' => 1],
        ];
    }

    public function ilegibles(int $pagina = 1): array
    {
        $datos = [
            ['documentoId' => 8144, 'paciente' => ['id' => 1, 'numeroHistoria' => '04-118-2297', 'apellidos' => 'Mamani Choque', 'nombres' => 'Rosa Elena'], 'sesionId' => 77, 'tipo' => 'otro', 'fechaDocumento' => null, 'creadoEn' => '2026-08-14T10:40:00Z'],
        ];

        return [
            'datos' => $datos,
            'meta' => ['pagina' => $pagina, 'porPagina' => 20, 'total' => count($datos), 'totalPaginas' => 1],
        ];
    }
}
