<?php

namespace App\Compartido\Errores;

use Illuminate\Contracts\Validation\Validator;

/**
 * Construye el `VALIDACION_ENTRADA` de `docs/errores/manejo-errores.md` a partir
 * de un validador de Laravel.
 *
 * Los errores sintácticos y de campo comparten un solo código con detalle por
 * campo, en vez de un código nuevo por cada regla. La regla se nombra en
 * español porque forma parte del detalle que la interfaz muestra.
 */
final class ErrorDeValidacion
{
    /** Regla de Laravel => nombre de la regla en el detalle del error. */
    private const REGLAS = [
        'Required' => 'requerido',
        'Present' => 'requerido',
        'Filled' => 'requerido',
        'Email' => 'formato',
        'Regex' => 'formato',
        'Date' => 'formato',
        'Max' => 'longitud_maxima',
        'Min' => 'longitud_minima',
        'Between' => 'longitud',
        'Size' => 'longitud',
        'String' => 'tipo',
        'Boolean' => 'tipo',
        'Integer' => 'tipo',
        'Numeric' => 'tipo',
        'Array' => 'tipo',
        'In' => 'valor_no_permitido',
        'Unique' => 'duplicado',
        'Exists' => 'no_encontrado',
    ];

    public static function desde(Validator $validador): ErrorDeAplicacion
    {
        $campos = [];

        foreach ($validador->failed() as $campo => $reglas) {
            foreach (array_keys($reglas) as $regla) {
                $campos[] = [
                    'campo' => $campo,
                    'regla' => self::REGLAS[$regla] ?? mb_strtolower($regla),
                    'mensaje' => $validador->errors()->first($campo),
                ];
            }
        }

        return new ErrorDeAplicacion(
            'VALIDACION_ENTRADA',
            'Revisa los datos ingresados.',
            ['campos' => $campos],
        );
    }
}
