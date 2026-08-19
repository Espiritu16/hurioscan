# Contrato — interfaces de servicio de aplicación

Autoridad: Arquitectura. Estado: **aprobado** — Arquitectura, 2026-08-19.

Este documento fija las **firmas PHP** que separan la interfaz de su implementación. No repite el detalle de cada operación: cada método referencia la operación ya aprobada en su contrato de dominio, que sigue siendo la fuente de las validaciones, las respuestas y los códigos de error.

## Por qué existen

El proyecto no expone API HTTP: los componentes Livewire llaman a los servicios de dominio en el mismo proceso (ver `docs/frontend/integracion.md`). Para que la línea frontend avance sin su backend, cada componente debe depender de una **interfaz**, y un binding por entorno debe elegir entre el doble de desarrollo y la implementación real. Es el mismo mecanismo que ADR-0002 ya estableció para `MotorOcr`, generalizado a los cuatro dominios.

Sin esta separación no se pueden cumplir a la vez las dos reglas vigentes: que el doble se active solo por configuración, y que ningún código de producción referencie el namespace `Dobles`.

## Ubicación y nomenclatura

- Interfaz: `app/Dominios/<Dominio>/Contratos/<Nombre>.php` → namespace `App\Dominios\<Dominio>\Contratos`.
  Mantiene domain-first: la interfaz de un dominio vive dentro de su dominio, no en una carpeta técnica compartida.
- Doble de desarrollo: `app/Compartido/Dobles/<Dominio>/<Nombre>Doble.php`, implementando esa interfaz.
- Implementación real (sprints `B`): dentro del dominio, fuera de `Contratos/`.
- Binding: `app/Providers/DoblesServiceProvider.php`, gobernado por `config/dobles.php`.

## Convenciones comunes

- **Retorno:** `array` asociativo con exactamente las claves que declara la respuesta de la operación en su contrato. La interfaz fija el nombre del método, sus parámetros y su tipo de retorno; **las claves las fija el contrato del dominio y no se duplican aquí** (referenciar, no copiar).
- **Errores:** ninguna operación devuelve un código de error como valor. Se lanza `App\Compartido\Errores\ErrorDeAplicacion`, que expone `getCodigo(): string` con un código de la taxonomía de `docs/errores/manejo-errores.md` y `getDetalle(): array` con su detalle. La vista traduce ese código al mensaje que corresponde.
- **Instantes:** todo instante viaja como cadena ISO-8601 en UTC (RNF-005). Las fechas locales sin zona (`fechaNacimiento`, `fechaDocumento`) viajan como `YYYY-MM-DD`.
- **Paginación:** los listados aceptan `int $pagina = 1` y devuelven su bloque `meta` según el contrato.
- **Actor:** ningún método recibe el usuario autenticado como parámetro; la implementación lo resuelve de la sesión. Los permisos por rol de `docs/requisitos/actores-permisos.md` se aplican en la implementación, no en la vista.

## `App\Dominios\Pacientes\Contratos\ServicioPacientes`

| Método | Operación del contrato |
|---|---|
| `buscar(string $termino = '', int $pagina = 1): array` | `GET /pacientes` |
| `registrar(array $datos): array` | `POST /pacientes` |
| `consultarDni(string $dni): array` | `POST /pacientes/consultar-dni` |
| `lineaDeTiempo(int $pacienteId, array $filtros = [], int $pagina = 1): array` | `GET /pacientes/{id}` |

`$datos` de `registrar` y `$filtros` de `lineaDeTiempo` llevan las claves que el contrato declara como request; la validación es responsabilidad de la implementación, nunca de la vista (RNF-010).

## `App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion`

| Método | Operación del contrato |
|---|---|
| `abrirSesion(int $pacienteId): array` | `POST /sesiones` |
| `sesionesPendientes(int $pagina = 1): array` | `GET /sesiones/pendientes` |
| `agregarHoja(int $sesionId, mixed $archivo, string $tipo, ?string $fechaDocumento = null): array` | `POST /sesiones/{id}/hojas` |
| `quitarHoja(int $sesionId, int $hojaId): void` | `DELETE /sesiones/{id}/hojas/{hoja}` |
| `enviarARevision(int $sesionId): array` | `POST /sesiones/{id}/enviar-a-revision` |
| `volverACaptura(int $sesionId): array` | `POST /sesiones/{id}/volver-a-captura` |
| `cerrarSesion(int $sesionId): array` | `POST /sesiones/{id}/cerrar` |
| `avance(): array` | `GET /avance` |

`$archivo` se tipa `mixed` porque en Livewire llega como `TemporaryUploadedFile` y en un test como `UploadedFile`; fijar una de las dos ataría la interfaz al mecanismo de transporte. `quitarHoja` devuelve `void` porque la operación responde 204 sin cuerpo.

## `App\Dominios\Documentos\Contratos\ServicioDocumentos`

| Método | Operación del contrato |
|---|---|
| `corregirTexto(int $documentoId, string $texto, int $version): array` | `PATCH /documentos/{id}/texto` |
| `marcar(int $documentoId, string $resultado): array` | `POST /documentos/{id}/marcar` |
| `reabrirRevision(int $documentoId): array` | `POST /documentos/{id}/reabrir-revision` |
| `reintentarOcr(int $documentoId): array` | `POST /documentos/{id}/reintentar-ocr` |
| `buscar(string $termino, array $filtros = [], int $pagina = 1): array` | `GET /buscar` |
| `ver(int $documentoId): array` | `GET /documentos/{id}` |
| `hojasDeSesion(int $sesionId): array` | `GET /sesiones/{id}/hojas` |
| `ilegibles(int $pagina = 1): array` | `GET /ilegibles` |

`corregirTexto` recibe `$version` de forma obligatoria: es el control de concurrencia optimista del contrato. Ante `VERSION_DESACTUALIZADA`, la vista muestra `detalle.textoActual` y pide decidir — **nunca reenvía con la versión nueva en silencio**.

**Fuera de esta interfaz:** `GET /documentos/{id}/imagen` entrega un binario con sus encabezados y no es un servicio de aplicación; se resuelve en su ruta al implementarse B06. Hasta entonces, la vista usa una imagen de ejemplo servida como recurso estático.

## `App\Dominios\Usuarios\Contratos\ServicioUsuarios`

| Método | Operación del contrato |
|---|---|
| `autenticar(string $email, string $password, bool $recordar = false): array` | `POST /acceder` |
| `salir(): void` | `POST /salir` |
| `listar(int $pagina = 1): array` | `GET /usuarios` |
| `crear(array $datos): array` | `POST /usuarios` |
| `actualizar(int $usuarioId, array $cambios): array` | `PATCH /usuarios/{id}` |
| `auditoria(array $filtros = [], int $pagina = 1): array` | `GET /auditoria` |

`GET /acceder` no aparece: solo renderiza el formulario y no ejecuta ninguna operación de dominio. Ningún método de `listar` ni de `auditoria` devuelve el hash de contraseña, como el contrato exige.

## Selección del doble

`config/dobles.php` declara un interruptor por dominio, leído de variables de entorno con `false` como valor por defecto:

```php
return [
    'pacientes'      => env('DOBLE_PACIENTES', false),
    'digitalizacion' => env('DOBLE_DIGITALIZACION', false),
    'documentos'     => env('DOBLE_DOCUMENTOS', false),
    'usuarios'       => env('DOBLE_USUARIOS', false),
];
```

`DoblesServiceProvider` liga cada interfaz a su doble **solo** cuando el interruptor está activo y el entorno es `local` o `testing`. En cualquier otro entorno el provider no liga nada, de modo que un binding faltante falla de forma visible en vez de servir datos de ejemplo en producción. Los interruptores se documentan en `.env.example` con valor `false`; ningún doble se activa por defecto.

Esto extiende el criterio que ADR-0002 ya fijó para `MotorOcr` y las sustituciones por entorno de `docs/frontend/integracion.md` § Cliente compartido, sin introducir un mecanismo nuevo.

## Verificación obligatoria antes de entregar

PHP no comprueba estáticamente que un método invocado sobre una interfaz esté declarado en ella: lo resuelve en runtime sobre el objeto concreto. Con un doble que sí lo tiene, el código pasa todas las pruebas y la divergencia solo aparece cuando llega la implementación real. Ya ocurrió una vez (ver el «Origen» de `GET /sesiones/{id}/hojas`).

Por eso, antes de entregar un sprint que consuma estas interfaces, se comparan los métodos invocados sobre servicios contra los declarados, y la lista de sobrantes debe quedar vacía:

```bash
grep -rhoE '\$[a-zA-Z]+->[a-zA-Z]+\(' app/Dominios/*/Componentes/*.php | sed 's/.*->//;s/(//' | sort -u > /tmp/invocados
grep -hoE 'public function [a-zA-Z]+' app/Dominios/*/Contratos/*.php | sed 's/public function //' | sort -u > /tmp/declarados
comm -13 /tmp/declarados /tmp/invocados
```

Un método que aparezca ahí es una de dos cosas: una llamada que no pertenece a un servicio (ruido del grep, se descarta a ojo) o una operación que falta declarar. Lo segundo se escala a Arquitectura; nunca se agrega al doble y se sigue.

## Qué implica para los sprints `B`

B01 a B07 implementan estas interfaces tal como están fijadas aquí: **no las redefinen**. Un cambio de firma es un cambio de contrato y vuelve a Arquitectura. Al existir la implementación real de un dominio, su interruptor de doble queda en `false` y el doble deja de usarse; el punto de integración de cada par `B`/`F` verifica justamente que el build real no cae de vuelta al doble.
