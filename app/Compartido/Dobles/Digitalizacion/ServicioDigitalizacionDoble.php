<?php

namespace App\Compartido\Dobles\Digitalizacion;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;

/**
 * Doble de desarrollo del dominio Digitalización (F03-UT-01).
 *
 * Cubre sesión abierta, sesión ya existente para el paciente, y hoja rechazada
 * por formato y por tamaño. Mantiene las hojas en memoria durante la vida del
 * proceso, para que la grilla de captura se comporte como la real.
 */
class ServicioDigitalizacionDoble implements ServicioDigitalizacion
{
    private const LIMITE_BYTES = 15 * 1024 * 1024;

    private const FORMATOS = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

    /** Paciente que ya tiene una sesión sin cerrar, para ver ese desenlace. */
    private const PACIENTE_CON_SESION_ABIERTA = 4;

    private const SESION_EXISTENTE = 77;

    /** @var array<int, array<int, array<string, mixed>>> sesionId => hojas */
    private array $hojas = [];

    public function abrirSesion(int $pacienteId): array
    {
        if ($pacienteId === self::PACIENTE_CON_SESION_ABIERTA) {
            throw new ErrorDeAplicacion(
                'SESION_YA_ABIERTA',
                'Este paciente ya tiene una sesión sin cerrar.',
                ['sesionExistenteId' => self::SESION_EXISTENTE],
            );
        }

        return [
            'id' => 101,
            'pacienteId' => $pacienteId,
            'operadorId' => 1,
            'estado' => 'ABIERTA',
            'creadoEn' => '2026-08-19T12:00:00Z',
        ];
    }

    public function sesionesPendientes(int $pagina = 1): array
    {
        $datos = [
            ['id' => 77, 'paciente' => ['id' => 4, 'numeroHistoria' => '04-118-1073', 'apellidos' => 'Zárate Pinto', 'nombres' => 'Óscar'], 'estado' => 'ABIERTA', 'hojas' => 12, 'hojasSinRevisar' => 12, 'creadoEn' => '2026-08-18T15:30:00Z'],
            ['id' => 76, 'paciente' => ['id' => 2, 'numeroHistoria' => '04-117-8840', 'apellidos' => 'Huamán Ríos', 'nombres' => 'Julio César'], 'estado' => 'EN_REVISION', 'hojas' => 22, 'hojasSinRevisar' => 5, 'creadoEn' => '2026-08-18T09:10:00Z'],
        ];

        return [
            'datos' => $datos,
            'meta' => ['pagina' => $pagina, 'porPagina' => 20, 'total' => count($datos), 'totalPaginas' => 1],
        ];
    }

    public function agregarHoja(int $sesionId, mixed $archivo, string $tipo, ?string $fechaDocumento = null): array
    {
        $mime = $this->mimeDe($archivo);
        $tamano = $this->tamanoDe($archivo);

        if (! in_array($mime, self::FORMATOS, true)) {
            throw new ErrorDeAplicacion(
                'HOJA_FORMATO_NO_SOPORTADO',
                'Ese archivo no es una imagen ni un PDF.',
            );
        }

        if ($tamano > self::LIMITE_BYTES) {
            throw new ErrorDeAplicacion(
                'HOJA_DEMASIADO_GRANDE',
                'La hoja pesa más de 15 MB. Vuelve a tomarla con menos resolución.',
            );
        }

        $this->hojas[$sesionId] ??= [];
        $orden = count($this->hojas[$sesionId]) + 1;

        $hoja = [
            'id' => 8000 + $orden,
            'orden' => $orden,
            'tipo' => $tipo,
            'fechaDocumento' => $fechaDocumento,
            'estadoRevision' => 'PENDIENTE_OCR',
            'creadoEn' => '2026-08-19T12:05:00Z',
        ];

        $this->hojas[$sesionId][] = $hoja;

        return $hoja;
    }

    public function quitarHoja(int $sesionId, int $hojaId): void
    {
        $hojas = array_values(array_filter(
            $this->hojas[$sesionId] ?? [],
            fn (array $h) => $h['id'] !== $hojaId,
        ));

        if (count($hojas) === count($this->hojas[$sesionId] ?? [])) {
            throw new ErrorDeAplicacion('RECURSO_NO_ENCONTRADO', 'Esa hoja ya no está en la sesión.');
        }

        // El orden de las hojas siguientes se recalcula, como en el contrato.
        foreach ($hojas as $indice => $hoja) {
            $hojas[$indice]['orden'] = $indice + 1;
        }

        $this->hojas[$sesionId] = $hojas;
    }

    /** Hojas ya capturadas de una sesión; sirve a la grilla de captura. */
    public function hojasDe(int $sesionId): array
    {
        return $this->hojas[$sesionId] ?? [];
    }

    public function enviarARevision(int $sesionId): array
    {
        if (($this->hojas[$sesionId] ?? []) === []) {
            throw new ErrorDeAplicacion('SESION_SIN_HOJAS', 'La sesión no tiene ninguna hoja capturada.');
        }

        return ['id' => $sesionId, 'estado' => 'EN_REVISION', 'enviadoARevisionEn' => '2026-08-19T12:30:00Z'];
    }

    public function volverACaptura(int $sesionId): array
    {
        return ['id' => $sesionId, 'estado' => 'ABIERTA', 'enviadoARevisionEn' => null];
    }

    public function cerrarSesion(int $sesionId): array
    {
        return [
            'id' => $sesionId,
            'estado' => 'CERRADA',
            'cerradoEn' => '2026-08-19T13:00:00Z',
            'resumen' => [
                'hojas' => 14,
                'correctas' => 10,
                'corregidas' => 3,
                'ilegibles' => 1,
                'porTipo' => ['hoja_atencion' => 6, 'receta' => 4, 'laboratorio' => 3, 'consentimiento' => 1],
            ],
        ];
    }

    /** El total del acervo no está configurado: el panel no inventa un %. */
    public function avance(): array
    {
        return [
            'foldersCerrados' => 1248,
            'totalFoldersAcervo' => null,
            'porcentaje' => null,
            'hojasProcesadas' => 18942,
            'hojasIlegibles' => 412,
            'ritmoSemanal' => 186,
            'sesionesRecientes' => [
                ['id' => 77, 'paciente' => ['numeroHistoria' => '04-118-2297', 'apellidos' => 'Mamani Choque', 'nombres' => 'Rosa Elena'], 'hojas' => 14, 'operador' => 'R. Quispe', 'estado' => 'EN_REVISION', 'creadoEn' => '2026-08-19T08:52:00Z'],
                ['id' => 76, 'paciente' => ['numeroHistoria' => '04-117-8840', 'apellidos' => 'Huamán Ríos', 'nombres' => 'Julio César'], 'hojas' => 22, 'operador' => 'R. Quispe', 'estado' => 'CERRADA', 'creadoEn' => '2026-08-19T08:14:00Z'],
                ['id' => 75, 'paciente' => ['numeroHistoria' => '04-116-4412', 'apellidos' => 'Ccalla Ancco', 'nombres' => 'Marina'], 'hojas' => 9, 'operador' => 'M. Fernández', 'estado' => 'CERRADA', 'creadoEn' => '2026-08-19T07:56:00Z'],
            ],
        ];
    }

    private function mimeDe(mixed $archivo): string
    {
        if (is_object($archivo) && method_exists($archivo, 'getMimeType')) {
            return (string) $archivo->getMimeType();
        }

        return is_array($archivo) ? ($archivo['mime'] ?? 'application/octet-stream') : 'application/octet-stream';
    }

    private function tamanoDe(mixed $archivo): int
    {
        if (is_object($archivo) && method_exists($archivo, 'getSize')) {
            return (int) $archivo->getSize();
        }

        return is_array($archivo) ? (int) ($archivo['tamano'] ?? 0) : 0;
    }
}
