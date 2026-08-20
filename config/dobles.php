<?php

/*
|--------------------------------------------------------------------------
| Dobles de desarrollo
|--------------------------------------------------------------------------
|
| Un interruptor por dominio. Mientras el backend de un dominio no exista, su
| interruptor puede activarse en local o en tests para que la interfaz quede
| ligada al doble de `app/Compartido/Dobles/`. Al existir la implementación
| real, el interruptor vuelve a `false` y el doble deja de usarse.
|
| Todos están en `false` por defecto a propósito: ningún doble se activa solo.
| Ver `docs/contratos/servicios-aplicacion.md` § Selección del doble.
|
*/

return [
    'pacientes' => env('DOBLE_PACIENTES', false),
    'digitalizacion' => env('DOBLE_DIGITALIZACION', false),
    'documentos' => env('DOBLE_DOCUMENTOS', false),
    'usuarios' => env('DOBLE_USUARIOS', false),
];
