<?php

namespace App\Dominios\Usuarios;

use App\Compartido\Autorizacion\PoliticaDeDominio;

/** Política de autorización del dominio Usuarios (RNF-013). */
class PoliticaUsuarios extends PoliticaDeDominio
{
    public function dominio(): string
    {
        return 'Usuarios';
    }
}
