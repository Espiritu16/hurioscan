<?php

namespace App\Dominios\Documentos\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use Livewire\Component;

/**
 * Visor de documento (F06-UT-04).
 *
 * Un documento ILEGIBLE dice explícitamente que no tiene texto asociado, en
 * vez de mostrar un panel vacío.
 */
class VisorDocumento extends Component
{
    public int $documentoId = 8142;

    /** carga | error | exito */
    public string $estado = 'carga';

    public ?string $error = null;

    public array $documento = [];

    public function mount(ServicioDocumentos $documentos): void
    {
        try {
            $this->documento = $documentos->ver($this->documentoId);
        } catch (ErrorDeAplicacion) {
            // No se distingue "no existe" de "no autorizado" (contrato).
            $this->estado = 'error';
            $this->error = 'No encontramos ese documento.';

            return;
        }

        $this->estado = 'exito';
    }

    /** El texto corregido manda sobre el extraído cuando existe. */
    public function getTextoProperty(): ?string
    {
        return $this->documento['textoCorregido'] ?? $this->documento['textoExtraido'] ?? null;
    }

    public function getEsIlegibleProperty(): bool
    {
        return ($this->documento['estadoRevision'] ?? null) === 'ILEGIBLE';
    }

    public function render()
    {
        return view('dominios.documentos.visor');
    }
}
