<?php

namespace App\Dominios\Documentos;

use App\Compartido\Autorizacion\PoliticaDeDominio;

/** Política de autorización del dominio Documentos (RNF-013). */
class PoliticaDocumentos extends PoliticaDeDominio
{
    public function dominio(): string
    {
        return 'Documentos';
    }
}
