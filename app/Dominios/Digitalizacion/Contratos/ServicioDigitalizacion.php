<?php

namespace App\Dominios\Digitalizacion\Contratos;

/**
 * Operaciones de aplicación del dominio Digitalización.
 *
 * Firmas fijadas por Arquitectura en `docs/contratos/servicios-aplicacion.md`;
 * el detalle de cada operación vive en `docs/contratos/digitalizacion.md`.
 */
interface ServicioDigitalizacion
{
    /** `POST /sesiones` */
    public function abrirSesion(int $pacienteId): array;

    /** `GET /sesiones/pendientes` */
    public function sesionesPendientes(int $pagina = 1): array;

    /**
     * `POST /sesiones/{id}/hojas`
     *
     * `$archivo` se tipa `mixed` porque en Livewire llega como
     * `TemporaryUploadedFile` y en un test como `UploadedFile`: fijar una de
     * las dos ataría la interfaz al mecanismo de transporte.
     */
    public function agregarHoja(int $sesionId, mixed $archivo, string $tipo, ?string $fechaDocumento = null): array;

    /** `DELETE /sesiones/{id}/hojas/{hoja}` — responde 204, sin cuerpo */
    public function quitarHoja(int $sesionId, int $hojaId): void;

    /** `POST /sesiones/{id}/enviar-a-revision` */
    public function enviarARevision(int $sesionId): array;

    /** `POST /sesiones/{id}/volver-a-captura` */
    public function volverACaptura(int $sesionId): array;

    /** `POST /sesiones/{id}/cerrar` */
    public function cerrarSesion(int $sesionId): array;

    /** `GET /avance` */
    public function avance(): array;
}
