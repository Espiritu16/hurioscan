<?php

namespace App\Dominios\Documentos\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Búsqueda por contenido con fragmento resaltado (F06-UT-02).
 *
 * El término y la página viajan en la URL, así que volver atrás conserva la
 * vista. El resaltado se aplica **después** de escapar el texto (RNF-012).
 */
class BusquedaContenido extends Component
{
    #[Url(as: 'q')]
    public string $termino = '';

    #[Url]
    public int $pagina = 1;

    #[Url]
    public string $tipo = '';

    /** idle | buscando | vacio | error | exito */
    public string $estado = 'idle';

    public ?string $error = null;

    public array $resultados = [];

    public array $meta = [];

    public function mount(ServicioDocumentos $documentos): void
    {
        if ($this->termino !== '') {
            $this->buscar($documentos);
        }
    }

    public function buscar(ServicioDocumentos $documentos): void
    {
        $this->estado = 'buscando';
        $this->error = null;

        try {
            $respuesta = $documentos->buscar($this->termino, ['tipo' => $this->tipo], $this->pagina);
        } catch (ErrorDeAplicacion $e) {
            if ($e->getCodigo() === 'BUSQUEDA_TERMINO_VACIO') {
                $this->estado = 'idle';
                $this->error = 'Escribe qué quieres buscar.';

                return;
            }

            $this->estado = 'error';
            $this->error = 'No se pudo completar la búsqueda.';

            return;
        }

        $this->resultados = $respuesta['datos'];
        $this->meta = $respuesta['meta'];
        $this->estado = $this->resultados === [] ? 'vacio' : 'exito';
    }

    public function updatedTipo(): void
    {
        $this->pagina = 1;

        if ($this->termino !== '') {
            $this->buscar(app(ServicioDocumentos::class));
        }
    }

    /**
     * Resalta el término dentro del fragmento. El orden importa: primero se
     * escapa todo el texto del documento, y solo después se insertan las
     * marcas de resaltado. Al revés, el contenido del documento podría
     * inyectar HTML en la vista (RNF-012).
     */
    public function resaltar(string $fragmento): HtmlString
    {
        $escapado = e($fragmento);

        if ($this->termino === '') {
            return new HtmlString($escapado);
        }

        $patron = '/'.preg_quote(e($this->termino), '/').'/iu';

        return new HtmlString(preg_replace(
            $patron,
            '<mark class="rounded bg-advertencia-suave px-0.5 text-tinta">$0</mark>',
            $escapado,
        ));
    }

    public function render()
    {
        return view('dominios.documentos.busqueda');
    }
}
