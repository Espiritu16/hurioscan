<?php

namespace App\Compartido\Dobles\Pacientes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;

/**
 * Doble de desarrollo del dominio Pacientes (F02-UT-01).
 *
 * Cubre los cuatro desenlaces de la consulta por DNI: paciente ya registrado,
 * datos traídos del proveedor, DNI inexistente y proveedor no disponible.
 */
class ServicioPacientesDoble implements ServicioPacientes
{
    private const PACIENTES = [
        ['id' => 1, 'numeroHistoria' => '04-118-2297', 'dni' => '41822703', 'apellidos' => 'Mamani Choque', 'nombres' => 'Rosa Elena', 'fechaNacimiento' => '1979-03-12', 'totalDocumentos' => 14],
        ['id' => 2, 'numeroHistoria' => '04-117-8840', 'dni' => '44903112', 'apellidos' => 'Huamán Ríos', 'nombres' => 'Julio César', 'fechaNacimiento' => '1986-09-02', 'totalDocumentos' => 22],
        ['id' => 3, 'numeroHistoria' => '04-116-4412', 'dni' => null, 'apellidos' => 'Ccalla Ancco', 'nombres' => 'Marina', 'fechaNacimiento' => null, 'totalDocumentos' => 9],
        ['id' => 4, 'numeroHistoria' => '04-118-1073', 'dni' => '09877431', 'apellidos' => 'Zárate Pinto', 'nombres' => 'Óscar', 'fechaNacimiento' => '1961-11-30', 'totalDocumentos' => 31],
    ];

    /**
     * DNIs de ejemplo del proveedor de identidad. Ninguno existe en RENIEC:
     * son ficticios a propósito.
     */
    private const PROVEEDOR = [
        '70112233' => ['apellidos' => 'Quispe Mamani', 'nombres' => 'Ana Lucía'],
        '70445566' => ['apellidos' => 'Ticona Flores', 'nombres' => 'Pedro Martín'],
    ];

    /** DNI reservado para forzar el desenlace de proveedor caído. */
    private const DNI_PROVEEDOR_CAIDO = '70999999';

    public function buscar(string $termino = '', int $pagina = 1): array
    {
        $porPagina = 20;
        $termino = trim($termino);

        $encontrados = $termino === ''
            ? self::PACIENTES
            : array_values(array_filter(
                self::PACIENTES,
                fn (array $p) => str_contains(mb_strtolower("{$p['numeroHistoria']} {$p['dni']} {$p['apellidos']} {$p['nombres']}"), mb_strtolower($termino)),
            ));

        $total = count($encontrados);

        return [
            'datos' => array_slice($encontrados, ($pagina - 1) * $porPagina, $porPagina),
            'meta' => [
                'pagina' => $pagina,
                'porPagina' => $porPagina,
                'total' => $total,
                'totalPaginas' => max(1, (int) ceil($total / $porPagina)),
            ],
        ];
    }

    public function registrar(array $datos): array
    {
        $historia = trim($datos['numeroHistoria'] ?? '');

        foreach (self::PACIENTES as $paciente) {
            if ($paciente['numeroHistoria'] === $historia) {
                throw new ErrorDeAplicacion(
                    'PACIENTE_HC_DUPLICADO',
                    'Ya existe un paciente con ese número de historia clínica.',
                    ['pacienteId' => $paciente['id']],
                );
            }
        }

        return [
            'id' => 99,
            'numeroHistoria' => $historia,
            'dni' => $datos['dni'] ?? null,
            'apellidos' => $datos['apellidos'] ?? '',
            'nombres' => $datos['nombres'] ?? '',
            'fechaNacimiento' => $datos['fechaNacimiento'] ?? null,
            'origenDatos' => $datos['origenDatos'] ?? 'manual',
            'creadoEn' => '2026-08-19T12:00:00Z',
        ];
    }

    /**
     * Busca primero en el archivo propio y solo consulta al proveedor si el
     * DNI no está registrado: un paciente ya digitalizado no gasta un crédito.
     */
    public function consultarDni(string $dni): array
    {
        $dni = trim($dni);

        foreach (self::PACIENTES as $paciente) {
            if ($paciente['dni'] === $dni) {
                return [
                    'pacienteExistente' => [
                        'id' => $paciente['id'],
                        'numeroHistoria' => $paciente['numeroHistoria'],
                        'apellidos' => $paciente['apellidos'],
                        'nombres' => $paciente['nombres'],
                    ],
                    'datos' => null,
                ];
            }
        }

        if ($dni === self::DNI_PROVEEDOR_CAIDO) {
            throw new ErrorDeAplicacion(
                'IDENTIDAD_PROVEEDOR_NO_DISPONIBLE',
                'No se pudo consultar el documento en este momento.',
            );
        }

        if (! isset(self::PROVEEDOR[$dni])) {
            throw new ErrorDeAplicacion(
                'IDENTIDAD_NO_ENCONTRADA',
                'No se encontró ese documento.',
            );
        }

        return [
            'pacienteExistente' => null,
            'datos' => [
                'dni' => $dni,
                'apellidos' => self::PROVEEDOR[$dni]['apellidos'],
                'nombres' => self::PROVEEDOR[$dni]['nombres'],
                'origen' => 'proveedor',
            ],
        ];
    }

    public function lineaDeTiempo(int $pacienteId, array $filtros = [], int $pagina = 1): array
    {
        $paciente = array_values(array_filter(self::PACIENTES, fn (array $p) => $p['id'] === $pacienteId))[0]
            ?? throw new ErrorDeAplicacion('RECURSO_NO_ENCONTRADO', 'El paciente no existe.');

        $documentos = [
            ['id' => 8142, 'tipo' => 'hoja_atencion', 'fechaDocumento' => '2021-06-09', 'estadoRevision' => 'CORRECTA', 'fragmento' => 'DIAGNÓSTICO: Hipertensión arterial esencial I10', 'digitalizadoEn' => '2026-08-14T10:20:00Z'],
            ['id' => 8143, 'tipo' => 'receta', 'fechaDocumento' => '2021-06-09', 'estadoRevision' => 'CORRECTA', 'fragmento' => 'Enalapril 10 mg, 60 tabletas', 'digitalizadoEn' => '2026-08-14T10:22:00Z'],
            ['id' => 8098, 'tipo' => 'laboratorio', 'fechaDocumento' => '2019-02-14', 'estadoRevision' => 'CORREGIDA', 'fragmento' => 'Perfil lipídico solicitado en control anual', 'digitalizadoEn' => '2026-08-12T09:05:00Z'],
            ['id' => 8055, 'tipo' => 'otro', 'fechaDocumento' => null, 'estadoRevision' => 'ILEGIBLE', 'fragmento' => null, 'digitalizadoEn' => '2026-08-11T16:40:00Z'],
        ];

        if (isset($filtros['tipo']) && $filtros['tipo'] !== '') {
            $documentos = array_values(array_filter($documentos, fn (array $d) => $d['tipo'] === $filtros['tipo']));
        }

        // Un documento sin fecha queda excluido cuando se aplica un rango,
        // salvo que se pidan explícitamente con `desde=sin-fecha`.
        if (($filtros['desde'] ?? '') === 'sin-fecha') {
            $documentos = array_values(array_filter($documentos, fn (array $d) => $d['fechaDocumento'] === null));
        } elseif (! empty($filtros['desde']) || ! empty($filtros['hasta'])) {
            $documentos = array_values(array_filter($documentos, function (array $d) use ($filtros) {
                if ($d['fechaDocumento'] === null) {
                    return false;
                }

                return (empty($filtros['desde']) || $d['fechaDocumento'] >= $filtros['desde'])
                    && (empty($filtros['hasta']) || $d['fechaDocumento'] <= $filtros['hasta']);
            }));
        }

        return [
            'paciente' => $paciente,
            'datos' => $documentos,
            'meta' => [
                'pagina' => $pagina,
                'porPagina' => 20,
                'total' => count($documentos),
                'totalPaginas' => 1,
            ],
        ];
    }
}
