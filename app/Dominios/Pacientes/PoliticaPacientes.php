<?php

namespace App\Dominios\Pacientes;

use App\Compartido\Autorizacion\PoliticaDeDominio;

/** Política de autorización del dominio Pacientes (RNF-013). */
class PoliticaPacientes extends PoliticaDeDominio
{
    public function dominio(): string
    {
        return 'Pacientes';
    }
}
