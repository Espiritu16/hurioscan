<?php

namespace App\Dominios\Digitalizacion;

use App\Compartido\Autorizacion\PoliticaDeDominio;

/** Política de autorización del dominio Digitalizacion (RNF-013). */
class PoliticaDigitalizacion extends PoliticaDeDominio
{
    public function dominio(): string
    {
        return 'Digitalizacion';
    }
}
