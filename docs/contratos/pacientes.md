# Contrato — dominio Pacientes

Autoridad: Arquitectura. Estado: `propuesto` hasta aprobación.
El proyecto no expone API HTTP pública: cada operación es una ruta web servida por un componente Livewire. La "Ruta real" es la ruta montada; el encabezado corto la nombra dentro de este documento.

**Criterio de paginación del dominio (aplica a todos los listados de este documento):** estilo **offset** (`pagina`/`porPagina`). Razón: las colecciones son moderadas y el acceso a una página arbitraria es un caso de uso real del personal de archivo, que recorre el acervo por tramos. El corrimiento de filas al insertar durante la paginación no es crítico aquí porque el ritmo de alta es bajo. Migrar a cursor sería un cambio breaking y exigiría subir la versión del contrato.

**Política de campos desconocidos (todo el documento):** se rechazan con `VALIDACION_ENTRADA`. No se ignoran silenciosamente.

---

## GET /pacientes
- Ruta real: `GET /pacientes`
- Path params: ninguno
- Query params:
  - `q?: string` (opcional) — término de búsqueda: número de historia, DNI o parte del nombre
  - `pagina?: integer` (opcional, default `1`, **base 1**)
  - `porPagina?: integer` (opcional, default `20`, máximo `100`)
  - `orden?: string` (opcional, default `apellidos:asc`) — valores permitidos: `apellidos:asc`, `apellidos:desc`, `numero_historia:asc`, `numero_historia:desc`, `created_at:desc`
- Orden por defecto: `apellidos asc, id asc` — `id` es el desempate único que garantiza posición determinística entre páginas
- Consistencia temporal: vista viva. No se promete snapshot: las altas de pacientes durante el recorrido pueden desplazar filas entre páginas.
- Parámetros inválidos: se rechazan siempre con `PARAMETRO_LISTADO_INVALIDO`. Única excepción documentada: `porPagina` por encima del máximo se acota a `100` en lugar de rechazarse.

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `q` (query) | string | opcional; ausente y vacío equivalen a "sin filtro" | Unicode libre; no se restringe a letras porque los apellidos pueden traer apóstrofes, guiones y tildes | 1–120 caracteres tras normalizar | trim exterior; colapso de espacios internos | ninguna | `VALIDACION_ENTRADA` | RF-001: búsqueda; derivado: tipo y límite |
| `pagina` (query) | entero | opcional, default 1 | dígitos, parseo completo (`3abc` se rechaza, no se lee como 3) | ≥ 1 | ninguna | ninguna | `PARAMETRO_LISTADO_INVALIDO` | derivado: inherente al tipo |
| `porPagina` (query) | entero | opcional, default 20 | dígitos, parseo completo | 1–100; por encima se acota a 100 | acotado al máximo | ninguna | `PARAMETRO_LISTADO_INVALIDO` | derivado; excepción de acotado declarada arriba |
| `orden` (query) | enum | opcional, default `apellidos:asc` | uno del conjunto cerrado listado arriba | no aplica — conjunto cerrado | ninguna | ninguna | `PARAMETRO_LISTADO_INVALIDO` | derivado: vocabulario cerrado |

- Response 200: `{ datos: Paciente[], meta: { pagina: number, porPagina: number, total: number, totalPaginas: number } }` donde `Paciente = { id: number, numeroHistoria: string, dni: string|null, apellidos: string, nombres: string, fechaNacimiento: string|null (fecha local `YYYY-MM-DD`, sin zona), totalDocumentos: number }`
- `total` se calcula sobre la misma colección ya acotada por permisos, no sobre la tabla completa
- Idempotencia: no aplica — operación de solo lectura
- Concurrencia: no aplica — operación de solo lectura
- Errores: ver `docs/errores/manejo-errores.md` → `VALIDACION_ENTRADA`, `PARAMETRO_LISTADO_INVALIDO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador`, `consulta`, `administrador`
- Versión del contrato: v1

---

## POST /pacientes
- Ruta real: `POST /pacientes`
- Path params: ninguno
- Query params: ninguno
- Request: `{ numeroHistoria: string (requerido), dni?: string (opcional), apellidos: string (requerido), nombres: string (requerido), fechaNacimiento?: string (opcional), origenDatos?: string (opcional, default `manual`) }`

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `numeroHistoria` (body) | string — **no número**: admite ceros iniciales y separadores | requerido, no null, no vacío | dígitos, guiones y espacios; `^[0-9][0-9\- ]{2,29}$` | 3–30 caracteres | trim exterior; colapso de espacios; se conserva el guion tal como se ingresa | único entre pacientes no eliminados | `VALIDACION_ENTRADA` (formato) · `PACIENTE_HC_DUPLICADO` (unicidad) | RF-001: presencia y unicidad; glosario: es cadena, no número; formato propuesto |
| `dni` (body) | string — **no número**: admite ceros iniciales | opcional; null y vacío se tratan igual y guardan null | exactamente 8 dígitos (`^[0-9]{8}$`) para DNI peruano | 8 caracteres | trim exterior | único entre pacientes no eliminados que tengan DNI | `VALIDACION_ENTRADA` · `PACIENTE_DNI_DUPLICADO` | RF-001; formato de 8 dígitos **propuesto** — confirmar si el establecimiento registra también carné de extranjería, que tiene otro formato |
| `apellidos` (body) | string (texto humano) | requerido, no null, no vacío tras trim | Unicode; se aceptan tildes, apóstrofes, guiones y espacios; no se restringe a `[A-Za-z]` | 2–120 caracteres | trim exterior; colapso de espacios internos | ninguna | `VALIDACION_ENTRADA` | RF-001; límite propuesto |
| `nombres` (body) | string (texto humano) | requerido, no null, no vacío tras trim | igual que `apellidos` | 2–120 caracteres | igual que `apellidos` | ninguna | `VALIDACION_ENTRADA` | RF-001; límite propuesto |
| `fechaNacimiento` (body) | fecha local sin hora | opcional; null permitido — un folder antiguo puede no tenerla | `YYYY-MM-DD`, calendario válido | no anterior a 1900-01-01; no posterior a hoy | ninguna — no se convierte a instante ni se le aplica zona | no puede ser futura | `VALIDACION_ENTRADA` | RF-001; rango propuesto |
| `origenDatos` (body) | enum | opcional, default `manual` | uno de `manual`, `proveedor` | conjunto cerrado | ninguna | el valor `proveedor` solo se acepta si los apellidos y nombres coinciden con los devueltos por la consulta previa de esa sesión; si el operador los editó, se guarda `manual` | `VALIDACION_ENTRADA` | RF-015: trazabilidad del origen |

- Response 201: `{ id: number, numeroHistoria: string, dni: string|null, apellidos: string, nombres: string, fechaNacimiento: string|null, origenDatos: string, creadoEn: string (ISO-8601 con offset UTC explícito, ej. 2026-08-18T14:30:00Z) }`
- Campos generados por servidor, nunca enlazados por asignación masiva aunque el cliente los envíe: `id`, `creadoEn`, `actualizadoEn`, `eliminadoEn`
- Idempotencia: **deduplicación (unique)** — la restricción `UNIQUE` sobre `numero_historia` impide crear el mismo paciente dos veces; un reintento devuelve `PACIENTE_HC_DUPLICADO`, no un duplicado silencioso. No reproduce la respuesta original, por lo que no es `requerida`.
- Concurrencia: no aplica — es una creación, no una edición de un recurso existente
- Errores: `VALIDACION_ENTRADA`, `PACIENTE_HC_DUPLICADO`, `PACIENTE_DNI_DUPLICADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador`, `administrador`. El rol `consulta` está denegado en la matriz.
- Versión del contrato: v1

---

## POST /pacientes/consultar-dni
- Ruta real: `POST /pacientes/consultar-dni` — trae los datos del titular desde el proveedor de identidad para precargar el formulario (RF-015)
- Path params: ninguno
- Query params: ninguno
- Request: `{ dni: string (requerido) }`
- **No persiste nada.** Devuelve datos para precargar el formulario; el alta sigue siendo `POST /pacientes`.

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `dni` (body) | string — **no número**: admite ceros iniciales | requerido, no null, no vacío | exactamente 8 dígitos (`^[0-9]{8}$`) | 8 caracteres | trim exterior | ninguna | `VALIDACION_ENTRADA` | RF-015; mismo formato que el campo `dni` del alta |

- Response 200: `{ dni: string, apellidos: string, nombres: string, origen: "proveedor" }` — solo estos campos. La dirección y el ubigeo que el proveedor incluye **no se solicitan ni se devuelven**: ningún requisito los necesita y traerlos sería recolección innecesaria de datos personales (ver `docs/integraciones/json-pe.md`).
- Response 404 con `IDENTIDAD_NO_ENCONTRADA`: el DNI no existe en la fuente. **No es un error del sistema**: la interfaz deja el formulario editable para carga manual.
- Response 503 con `IDENTIDAD_PROVEEDOR_NO_DISPONIBLE`: el proveedor no respondió, la credencial es inválida o se agotaron los créditos. Al operador se le informa lo mismo en los tres casos, porque ninguno puede resolverlo; la distinción real queda en `LogError` con el token enmascarado.
- **En ningún caso esta operación impide registrar al paciente.** Es una ayuda de carga (RF-015).
- Idempotencia: **deduplicación** — cada llamada consume un crédito del proveedor, así que una consulta repetida del mismo DNI dentro de la misma sesión de registro devuelve el resultado ya obtenido en vez de volver a llamar. La deduplicación es por sesión de usuario y no se comparte entre usuarios: una caché compartida de datos de identidad sería almacenamiento de datos personales que ningún requisito pide.
- Concurrencia: no aplica — no modifica ningún recurso
- Errores: `VALIDACION_ENTRADA`, `IDENTIDAD_NO_ENCONTRADA`, `IDENTIDAD_PROVEEDOR_NO_DISPONIBLE`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador`, `administrador`. El rol `consulta` está denegado: no registra pacientes, así que no tiene motivo para consultar identidades.
- Versión del contrato: v1

---

## GET /pacientes/{id}
- Ruta real: `GET /pacientes/{id}` — línea de tiempo de documentos del paciente (RF-008)
- Path params: `id: number` (entero positivo; un valor no numérico produce `VALIDACION_ENTRADA`, un id inexistente produce `RECURSO_NO_ENCONTRADO`)
- Query params:
  - `tipo?: string` (opcional) — igualdad; valores del conjunto cerrado `hoja_atencion`, `receta`, `laboratorio`, `epicrisis`, `consentimiento`, `otro`
  - `desde?: string` / `hasta?: string` (opcionales) — rango sobre `fechaDocumento`, formato `YYYY-MM-DD`
  - `pagina?`, `porPagina?`, `orden?` — mismas reglas que `GET /pacientes`; `orden` admite `fecha_documento:desc` (default), `fecha_documento:asc`, `created_at:desc`
- Orden por defecto: `fecha_documento desc, id desc` — con desempate único por `id`. Los documentos con `fechaDocumento` nula se ordenan al final.
- Semántica de `null` en el filtro de fecha: un documento sin `fechaDocumento` queda **excluido** cuando se aplica `desde` o `hasta`. Para pedirlos explícitamente existe `desde=sin-fecha`, que devuelve solo los que no tienen fecha legible.
- Consistencia temporal: vista viva
- Response 200: `{ paciente: Paciente, datos: Documento[], meta: { pagina, porPagina, total, totalPaginas } }` donde `Documento = { id, tipo, fechaDocumento: string|null, estadoRevision: string, fragmento: string|null, digitalizadoEn: string (ISO-8601 UTC) }`
- El alcance de permisos se aplica **antes** de calcular `total`: un rol `consulta` no ve en el conteo los documentos de sesiones aún abiertas
- Idempotencia: no aplica — solo lectura
- Concurrencia: no aplica — solo lectura
- Errores: `VALIDACION_ENTRADA`, `PARAMETRO_LISTADO_INVALIDO`, `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador`, `consulta`, `administrador`
- Versión del contrato: v1

Aprobado por (Arquitectura): pendiente — fecha: pendiente
