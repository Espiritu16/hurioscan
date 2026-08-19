<?php

namespace App\Dominios\Pacientes\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;
use Livewire\Component;

/**
 * Alta de paciente con autocompletado por DNI (F02-UT-03).
 *
 * Los cuatro desenlaces de la consulta se ven distintos, y en los cuatro el
 * formulario queda utilizable: la consulta de identidad nunca bloquea el alta.
 */
class FormularioPaciente extends Component
{
    public string $dni = '';

    public string $numeroHistoria = '';

    public string $apellidos = '';

    public string $nombres = '';

    public string $fechaNacimiento = '';

    /** manual | proveedor — vuelve a `manual` si la persona edita los datos. */
    public string $origenDatos = 'manual';

    /** idle | consultando | ya_registrado | precargado | no_encontrado | proveedor_no_disponible */
    public string $estadoConsulta = 'idle';

    public ?string $avisoConsulta = null;

    public ?array $pacienteExistente = null;

    public ?string $errorAlta = null;

    public bool $guardado = false;

    /**
     * El botón se deshabilita mientras la consulta corre para no gastar dos
     * créditos con un doble clic.
     */
    public function consultarDni(ServicioPacientes $pacientes): void
    {
        $this->estadoConsulta = 'consultando';
        $this->avisoConsulta = null;
        $this->pacienteExistente = null;

        try {
            $respuesta = $pacientes->consultarDni($this->dni);
        } catch (ErrorDeAplicacion $e) {
            // Los dos fallos dejan el formulario completo para carga manual.
            $this->estadoConsulta = match ($e->getCodigo()) {
                'IDENTIDAD_NO_ENCONTRADA' => 'no_encontrado',
                default => 'proveedor_no_disponible',
            };
            $this->avisoConsulta = $this->estadoConsulta === 'no_encontrado'
                ? 'No encontramos ese documento. Escribe los datos a mano.'
                : 'No se pudo consultar el documento ahora. Escribe los datos a mano.';

            return;
        }

        if ($respuesta['pacienteExistente'] !== null) {
            // No se continúa con el alta: se ofrece abrir su folder.
            $this->estadoConsulta = 'ya_registrado';
            $this->pacienteExistente = $respuesta['pacienteExistente'];
            $this->avisoConsulta = 'Este documento ya está registrado.';

            return;
        }

        // Los campos se precargan y siguen siendo editables.
        $this->apellidos = $respuesta['datos']['apellidos'];
        $this->nombres = $respuesta['datos']['nombres'];
        $this->origenDatos = 'proveedor';
        $this->estadoConsulta = 'precargado';
        $this->avisoConsulta = 'Datos traídos del proveedor. Puedes corregirlos.';
    }

    /** Si la persona edita lo precargado, el origen deja de ser el proveedor. */
    public function updatedApellidos(): void
    {
        $this->origenDatos = 'manual';
    }

    public function updatedNombres(): void
    {
        $this->origenDatos = 'manual';
    }

    public function guardar(ServicioPacientes $pacientes): void
    {
        $this->errorAlta = null;

        try {
            $pacientes->registrar([
                'numeroHistoria' => $this->numeroHistoria,
                'dni' => $this->dni !== '' ? $this->dni : null,
                'apellidos' => $this->apellidos,
                'nombres' => $this->nombres,
                'fechaNacimiento' => $this->fechaNacimiento !== '' ? $this->fechaNacimiento : null,
                'origenDatos' => $this->origenDatos,
            ]);
        } catch (ErrorDeAplicacion $e) {
            $this->errorAlta = match ($e->getCodigo()) {
                'PACIENTE_HC_DUPLICADO' => 'Ya existe un paciente con ese número de historia clínica.',
                'PACIENTE_DNI_DUPLICADO' => 'Ya existe un paciente con ese documento.',
                default => 'Revisa los datos ingresados.',
            };

            return;
        }

        $this->guardado = true;
    }

    public function render()
    {
        return view('dominios.pacientes.formulario');
    }
}
