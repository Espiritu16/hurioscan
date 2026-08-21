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
| `autenticar(string $email, #[\SensitiveParameter] string $password, bool $recordar = false): array` | `POST /acceder` |
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

Por eso, antes de entregar un sprint que consuma estas interfaces, **se compara cada doble contra su interfaz por reflexión** y la diferencia debe quedar vacía en los cuatro pares:

```php
// Para cada par: los métodos públicos del doble menos los declarados en su interfaz.
$doble = new ReflectionClass($claseDoble);
$interfaz = new ReflectionClass($claseInterfaz);
$publicos = array_column($doble->getMethods(ReflectionMethod::IS_PUBLIC), 'name');
$declarados = array_column($interfaz->getMethods(), 'name');
$sobrantes = array_diff($publicos, $declarados, ['__construct']);
```

Un método que aparezca ahí es una operación que falta declarar. Se escala a Arquitectura; **nunca se agrega al doble y se sigue**.

Este criterio reemplaza a una comparación anterior por `grep` de métodos invocados, que solo veía los que alguien llamaba: la reflexión detecta además los sobrantes que nadie invoca todavía, y que el backend puede leer como parte del contrato. La mejora vino de la línea frontend al aplicar la versión anterior — encontró con ella un segundo método fuera de contrato que el `grep` no veía.

## Qué implica para los sprints `B`

B01 a B07 implementan estas interfaces tal como están fijadas aquí: **no las redefinen**. Un cambio de firma es un cambio de contrato y vuelve a Arquitectura. Al existir la implementación real de un dominio, su interruptor de doble queda en `false` y el doble deja de usarse; el punto de integración de cada par `B`/`F` verifica justamente que el build real no cae de vuelta al doble.


## Parámetros sensibles en las firmas — criterio del proyecto

**Decisión de Arquitectura, 2026-08-21.** Todo parámetro que transporte una
credencial o un dato sensible se marca con el atributo nativo
`#[\SensitiveParameter]` (PHP 8.2+; este proyecto corre 8.5.9). **Forma parte de la
firma, así que es contrato:** un sprint lo implementa tal cual, no lo redefine ni lo
omite.

### Por qué, y qué problema cierra

`QA-B01-01` y `QA-B01-02` fueron el mismo defecto a dos profundidades: los stack
traces de PHP incluyen **los valores de los argumentos de cada frame**, y
`autenticar()` recibía la contraseña como cadena suelta, así que cualquier excepción
dentro de esa llamada la escribía en claro en el log. DevOps lo tapó en la capa de
entorno con `zend.exception_ignore_args=1`, que funciona pero es una manta: **ningún
trace de la aplicación conserva ya los valores de sus argumentos.**

`#[\SensitiveParameter]` es quirúrgico donde la directiva es global. Medido sobre
PHP 8.5.9, con la directiva **apagada**:

| | contraseña en `getTrace()` crudo | resto de argumentos |
|---|---|---|
| Sin marcar | **fuga** | conservados |
| `zend.exception_ignore_args=1` | protegida | **perdidos** |
| `#[\SensitiveParameter]` | protegida | **conservados** |

El frame queda como `autenticar('operador@hurios...', Object(SensitiveParameterValue), false)`:
el correo sigue ahí para diagnosticar, la contraseña no.

### Las dos capas se conservan, y no es redundancia

La directiva de entorno **no se retira**. Son dos defensas con alcances distintos:

- el **atributo** protege lo que alguien declaró sensible, y conserva el resto del
  trace — es la capa que documenta la intención en la propia firma;
- la **directiva** protege también lo que nadie marcó, que es exactamente el modo de
  falla esperable cuando B02–B07 añadan parámetros nuevos.

Retirar la directiva para recuperar diagnóstico es una conversación legítima, pero
exige antes que **todo** parámetro sensible esté marcado, y eso no se puede afirmar
hoy. Mientras tanto rige el mismo criterio de las capas de subida: la capa de abajo
no confía en que la de arriba haya hecho su trabajo.

### Qué marcar, en B02–B07

Contraseñas y su confirmación; tokens, claves de API y credenciales de proveedores
externos (`docs/integraciones/json-pe.md`); y cualquier parámetro que un RNF de
seguridad declare no divulgable. **Ante la duda, marcar**: el coste es perder ese
argumento en un trace, y el de no marcarlo es escribirlo en el log.

**No** se marca lo que ya es público en el propio sistema —un identificador, un
correo, un número de historia clínica—: marcarlo de más degrada el diagnóstico sin
proteger nada.

### Cómo se verifica

Como todo en este proyecto: **provocándolo, no leyéndolo**. Una prueba que marque el
defecto de vuelta —quitar el atributo— y confirme que la contraseña reaparece en el
trace. Con la directiva de entorno activa esa prueba **no puede fallar**, porque la
manta la tapa igual, así que debe ejercerse con `zend.exception_ignore_args` apagado
explícitamente. Es el quinto caso del patrón de cobertura ficticia del proyecto, y
esta vez se anticipa en lugar de descubrirse.
