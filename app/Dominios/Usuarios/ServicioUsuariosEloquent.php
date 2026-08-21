<?php

namespace App\Dominios\Usuarios;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Compartido\Errores\ErrorDeValidacion;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use LogicException;

/**
 * Implementación real del dominio Usuarios (B01).
 *
 * Las firmas las fija `docs/contratos/servicios-aplicacion.md` y el detalle de
 * cada operación vive en `docs/contratos/usuarios.md`: se implementan, no se
 * redefinen.
 *
 * B01 cubre `autenticar` y `salir` (RF-011). La gestión de usuarios y la
 * consulta de auditoría son de B07 según el roadmap; sus métodos existen porque
 * la interfaz los declara y fallan de forma ruidosa, que es preferible a
 * devolver un listado vacío que se lee como «no hay nada».
 */
class ServicioUsuariosEloquent implements ServicioUsuarios
{
    /**
     * Mensaje único de `NO_AUTENTICADO`.
     *
     * Credencial inválida, usuario inexistente y usuario inactivo comparten
     * este texto **exacto**: distinguirlos revelaría qué correos están
     * registrados y cuáles fueron dados de baja.
     */
    private const MENSAJE_NO_AUTENTICADO = 'Correo o contraseña incorrectos.';

    public function autenticar(string $email, string $password, bool $recordar = false): array
    {
        $datos = $this->validarCredenciales($email, $password);

        // Las credenciales llegan hasta aquí y no se guardan en ninguna
        // propiedad: la contraseña no debe sobrevivir a la llamada ni aparecer
        // en un volcado de la excepción (RNF-014).
        $entro = Auth::attempt([
            'email' => $datos['email'],
            'password' => $datos['password'],
            'activo' => true,
        ], $recordar);

        if (! $entro) {
            throw new ErrorDeAplicacion('NO_AUTENTICADO', self::MENSAJE_NO_AUTENTICADO);
        }

        // Renovar el identificador de sesión al autenticar cierra la fijación
        // de sesión: sin esto, un identificador conocido antes de entrar sigue
        // siendo válido después.
        session()->regenerate();

        return $this->comoRespuesta(Auth::user());
    }

    public function salir(): void
    {
        Auth::logout();

        // Invalidar además de cerrar sesión: `logout()` olvida al usuario, pero
        // la sesión —y su token— seguirían vivos.
        session()->invalidate();
        session()->regenerateToken();
    }

    public function listar(int $pagina = 1): array
    {
        throw $this->pendienteDeB07('listar');
    }

    public function crear(array $datos): array
    {
        throw $this->pendienteDeB07('crear');
    }

    public function actualizar(int $usuarioId, array $cambios): array
    {
        throw $this->pendienteDeB07('actualizar');
    }

    public function auditoria(array $filtros = [], int $pagina = 1): array
    {
        throw $this->pendienteDeB07('auditoria');
    }

    /**
     * Validación de `POST /acceder` según la matriz del contrato.
     *
     * Vive en la implementación y no en la vista (RNF-010): el componente
     * Livewire no es el único llamador.
     *
     * `email` se normaliza con trim y a minúsculas —el contrato pide comparación
     * insensible a mayúsculas y el alta guarda el correo en minúsculas—.
     * `password` **no** se normaliza: un espacio puede ser parte de la
     * contraseña.
     *
     * @return array{email: string, password: string}
     */
    private function validarCredenciales(string $email, string $password): array
    {
        $validador = Validator::make(
            ['email' => trim($email), 'password' => $password],
            [
                'email' => ['required', 'string', 'email', 'max:180'],
                'password' => ['required', 'string', 'min:8', 'max:200'],
            ],
            [
                'email.required' => 'Ingresa tu correo.',
                'email.email' => 'El correo no tiene una forma válida.',
                'email.max' => 'El correo no puede superar los 180 caracteres.',
                'password.required' => 'Ingresa tu contraseña.',
                'password.min' => 'La contraseña tiene al menos 8 caracteres.',
                'password.max' => 'La contraseña no puede superar los 200 caracteres.',
            ],
        );

        if ($validador->fails()) {
            throw ErrorDeValidacion::desde($validador);
        }

        return [
            'email' => mb_strtolower(trim($email)),
            'password' => $password,
        ];
    }

    /** @return array{id: int, nombre: string, email: string, rol: string, activo: bool} */
    private function comoRespuesta(Usuario $usuario): array
    {
        return [
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'email' => $usuario->email,
            'rol' => $usuario->rol,
            'activo' => $usuario->activo,
        ];
    }

    private function pendienteDeB07(string $operacion): LogicException
    {
        return new LogicException(
            "«{$operacion}» del dominio Usuarios la implementa B07 (gestión de usuarios y auditoría). ".
            'Hasta entonces la pantalla que la consume se sirve con el doble de desarrollo.'
        );
    }
}
