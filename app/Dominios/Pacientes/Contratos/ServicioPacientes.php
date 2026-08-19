<?php

namespace App\Dominios\Pacientes\Contratos;

/**
 * Operaciones de aplicación del dominio Pacientes.
 *
 * Firmas fijadas por Arquitectura en `docs/contratos/servicios-aplicacion.md`;
 * el detalle de cada operación vive en `docs/contratos/pacientes.md`. La
 * validación de `$datos` y `$filtros` es responsabilidad de la implementación,
 * nunca de la vista (RNF-010).
 */
interface ServicioPacientes
{
    /** `GET /pacientes` */
    public function buscar(string $termino = '', int $pagina = 1): array;

    /** `POST /pacientes` */
    public function registrar(array $datos): array;

    /** `POST /pacientes/consultar-dni` */
    public function consultarDni(string $dni): array;

    /** `GET /pacientes/{id}` */
    public function lineaDeTiempo(int $pacienteId, array $filtros = [], int $pagina = 1): array;
}
