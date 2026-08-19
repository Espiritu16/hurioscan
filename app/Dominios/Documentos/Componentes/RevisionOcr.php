<?php

namespace App\Dominios\Documentos\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use Livewire\Component;

/**
 * Revisión del texto extraído (F05-UT-02 y F05-UT-03).
 *
 * Imagen y texto en paralelo; en 360 px los paneles se apilan. Una hoja en
 * `PENDIENTE_OCR` dice que el OCR sigue corriendo en vez de mostrar un panel
 * vacío. Un conflicto de versión muestra el texto vigente y deja decidir.
 */
class RevisionOcr extends Component
{
    public int $sesionId = 77;

    public array $hojas = [];

    public int $indice = 0;

    public string $texto = '';

    public int $version = 1;

    public ?string $aviso = null;

    /** Texto vigente cuando hay conflicto; null si no lo hay. */
    public ?string $textoEnConflicto = null;

    public ?int $versionEnConflicto = null;

    public function mount(ServicioDocumentos $documentos): void
    {
        $this->hojas = $documentos->hojasDeSesion($this->sesionId);
        $this->cargarHoja();
    }

    public function getHojaProperty(): array
    {
        return $this->hojas[$this->indice] ?? [];
    }

    /** Progreso real: hojas ya resueltas sobre el total. */
    public function getRevisadasProperty(): int
    {
        return count(array_filter(
            $this->hojas,
            fn (array $h) => in_array($h['estadoRevision'], ['CORRECTA', 'CORREGIDA', 'ILEGIBLE'], true),
        ));
    }

    public function irA(int $indice): void
    {
        $this->indice = max(0, min($indice, count($this->hojas) - 1));
        $this->cargarHoja();
    }

    public function guardar(ServicioDocumentos $documentos): void
    {
        $this->aviso = null;
        $this->textoEnConflicto = null;

        try {
            $respuesta = $documentos->corregirTexto($this->hoja['id'], $this->texto, $this->version);
        } catch (ErrorDeAplicacion $e) {
            if ($e->getCodigo() === 'VERSION_DESACTUALIZADA') {
                // Se muestra el texto vigente y decide la persona.
                $this->textoEnConflicto = $e->getDetalle()['textoActual'] ?? null;
                $this->versionEnConflicto = $e->getDetalle()['version'] ?? null;
                $this->aviso = 'Alguien más corrigió esta hoja mientras la editabas.';

                return;
            }

            $this->aviso = 'No se pudo guardar la corrección.';

            return;
        }

        $this->version = $respuesta['version'];
        $this->aviso = 'Corrección guardada.';
    }

    /** Resolver el conflicto es una decisión explícita, nunca automática. */
    public function conservarMiTexto(ServicioDocumentos $documentos): void
    {
        $this->version = $this->versionEnConflicto ?? $this->version;
        $this->textoEnConflicto = null;
        $this->guardar($documentos);
    }

    public function tomarTextoVigente(): void
    {
        $this->texto = $this->textoEnConflicto ?? $this->texto;
        $this->version = $this->versionEnConflicto ?? $this->version;
        $this->textoEnConflicto = null;
        $this->aviso = 'Se cargó el texto vigente. Revísalo antes de guardar.';
    }

    public function marcar(string $resultado, ServicioDocumentos $documentos): void
    {
        $this->aviso = null;

        try {
            $respuesta = $documentos->marcar($this->hoja['id'], $resultado);
        } catch (ErrorDeAplicacion $e) {
            $this->aviso = $e->getMessage();

            return;
        }

        $this->hojas[$this->indice]['estadoRevision'] = $respuesta['estadoRevision'];

        // Marcar avanza a la hoja siguiente.
        if ($this->indice < count($this->hojas) - 1) {
            $this->irA($this->indice + 1);
        }
    }

    public function render()
    {
        return view('dominios.documentos.revision');
    }

    private function cargarHoja(): void
    {
        $hoja = $this->hoja;
        $this->texto = $hoja['textoCorregido'] ?? $hoja['textoExtraido'] ?? '';
        $this->version = $hoja['version'] ?? 1;
        $this->aviso = null;
        $this->textoEnConflicto = null;
    }
}
