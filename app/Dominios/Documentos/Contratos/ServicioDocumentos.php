<?php

namespace App\Dominios\Documentos\Contratos;

/**
 * Operaciones de aplicación del dominio Documentos.
 *
 * Firmas fijadas por Arquitectura en `docs/contratos/servicios-aplicacion.md`;
 * el detalle de cada operación vive en `docs/contratos/documentos.md`.
 *
 * `GET /documentos/{id}/imagen` queda fuera de esta interfaz: entrega un
 * binario con sus encabezados y se resuelve en su ruta al implementarse B06.
 */
interface ServicioDocumentos
{
    /**
     * `PATCH /documentos/{id}/texto`
     *
     * `$version` es obligatoria: es el control de concurrencia optimista del
     * contrato. Ante `VERSION_DESACTUALIZADA` la vista muestra el texto vigente
     * y pide decidir; nunca reenvía con la versión nueva en silencio.
     */
    public function corregirTexto(int $documentoId, string $texto, int $version): array;

    /** `POST /documentos/{id}/marcar` */
    public function marcar(int $documentoId, string $resultado): array;

    /** `POST /documentos/{id}/reabrir-revision` */
    public function reabrirRevision(int $documentoId): array;

    /** `POST /documentos/{id}/reintentar-ocr` */
    public function reintentarOcr(int $documentoId): array;

    /** `GET /buscar` */
    public function buscar(string $termino, array $filtros = [], int $pagina = 1): array;

    /** `GET /documentos/{id}` */
    public function ver(int $documentoId): array;

    /** `GET /ilegibles` */
    public function ilegibles(int $pagina = 1): array;
}
