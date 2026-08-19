<?php

namespace App\Dominios\Digitalizacion\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Captura de hojas del folder (F03-UT-03).
 *
 * Una hoja rechazada muestra su motivo sin perder las ya capturadas: el error
 * vive en la tarjeta de esa hoja, no en la vista entera.
 */
class CapturaHojas extends Component
{
    use WithFileUploads;

    public int $sesionId = 101;

    public array $paciente = [
        'nombre' => 'Mamani Choque, Rosa Elena',
        'historia' => '04-118-2297',
    ];

    public array $hojas = [];

    /** Las tres vías de captura: cámara, galería y selector de archivos. */
    public $archivosCamara = [];

    public $archivosGaleria = [];

    public $archivos = [];

    /** Rechazos por hoja: cada uno conserva el nombre y el motivo. */
    public array $rechazos = [];

    public string $tipo = 'hoja_atencion';

    public string $fechaDocumento = '';

    public ?string $errorEnvio = null;

    public bool $enviadaARevision = false;

    /** Las tres vías desembocan en el mismo camino de captura. */
    public function updatedArchivosCamara(): void
    {
        $this->agregar($this->archivosCamara, app(ServicioDigitalizacion::class));
        $this->archivosCamara = [];
    }

    public function updatedArchivosGaleria(): void
    {
        $this->agregar($this->archivosGaleria, app(ServicioDigitalizacion::class));
        $this->archivosGaleria = [];
    }

    public function updatedArchivos(): void
    {
        $this->agregar($this->archivos, app(ServicioDigitalizacion::class));
        $this->archivos = [];
    }

    public function agregar($archivos, ServicioDigitalizacion $digitalizacion): void
    {
        foreach ((array) $archivos as $archivo) {
            try {
                // El tipo y la fecha se heredan de la hoja anterior.
                $hoja = $digitalizacion->agregarHoja(
                    $this->sesionId,
                    $archivo,
                    $this->tipo,
                    $this->fechaDocumento !== '' ? $this->fechaDocumento : null,
                );
            } catch (ErrorDeAplicacion $e) {
                $this->rechazos[] = [
                    'nombre' => $this->nombreDe($archivo),
                    'motivo' => match ($e->getCodigo()) {
                        'HOJA_FORMATO_NO_SOPORTADO' => 'Formato no admitido. Usa JPG, PNG, WebP o PDF.',
                        'HOJA_DEMASIADO_GRANDE' => 'Pesa más de 15 MB. Vuelve a tomarla con menos resolución.',
                        default => 'No se pudo agregar esta hoja.',
                    },
                ];

                continue;
            }

            $this->hojas[] = $hoja;
        }
    }

    public function quitar(int $hojaId, ServicioDigitalizacion $digitalizacion): void
    {
        try {
            $digitalizacion->quitarHoja($this->sesionId, $hojaId);
        } catch (ErrorDeAplicacion) {
            // Quitar una hoja que ya no está deja la vista como está.
        }

        $this->hojas = array_values(array_filter($this->hojas, fn (array $h) => $h['id'] !== $hojaId));

        foreach ($this->hojas as $indice => $hoja) {
            $this->hojas[$indice]['orden'] = $indice + 1;
        }
    }

    public function descartarRechazo(int $indice): void
    {
        unset($this->rechazos[$indice]);
        $this->rechazos = array_values($this->rechazos);
    }

    public function enviarARevision(ServicioDigitalizacion $digitalizacion): void
    {
        $this->errorEnvio = null;

        try {
            $digitalizacion->enviarARevision($this->sesionId);
        } catch (ErrorDeAplicacion $e) {
            $this->errorEnvio = $e->getCodigo() === 'SESION_SIN_HOJAS'
                ? 'Captura al menos una hoja antes de continuar a revisión.'
                : 'No se pudo continuar a revisión.';

            return;
        }

        $this->enviadaARevision = true;
    }

    public function render()
    {
        return view('dominios.digitalizacion.captura');
    }

    private function nombreDe(mixed $archivo): string
    {
        if (is_object($archivo) && method_exists($archivo, 'getClientOriginalName')) {
            return (string) $archivo->getClientOriginalName();
        }

        return is_array($archivo) ? ($archivo['nombre'] ?? 'hoja') : 'hoja';
    }
}
