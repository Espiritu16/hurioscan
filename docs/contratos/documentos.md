# Contrato — dominio Documentos

Autoridad: Arquitectura. Estado: `propuesto` hasta aprobación.
Operaciones servidas por componentes Livewire sobre rutas web; no hay API HTTP pública.

**Paginación:** mismo criterio offset del resto del proyecto.
**Campos desconocidos:** se rechazan con `VALIDACION_ENTRADA`.

---

## PATCH /documentos/{id}/texto
- Ruta real: `PATCH /documentos/{id}/texto` — corrige el texto extraído (RF-005)
- Path params: `id: number`
- Request: `{ texto: string (requerido), version: number (requerido) }`

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `id` (path) | identificador entero | requerido | dígitos, parseo completo | ≥ 1 | ninguna | el documento existe, su sesión es propia y no está `CERRADA` | `VALIDACION_ENTRADA` · `RECURSO_NO_ENCONTRADO` · `SESION_CERRADA_NO_MODIFICABLE` | RF-005; derivado |
| `texto` (body) | string (texto libre) | requerido; se admite string vacío — significa "la hoja no tiene texto legible", distinto de ausente | Unicode libre, incluidos saltos de línea; **no se sanea ni se filtra**: es el contenido del documento y debe conservarse tal cual | máximo 100 000 caracteres | trim exterior únicamente; los saltos de línea internos se conservan porque reflejan la estructura del papel | ninguna | `VALIDACION_ENTRADA` | RF-005; límite **propuesto** — deriva de una página densa a doble columna, confirmar contra documentos reales |
| `version` (body) | entero | requerido, no null | dígitos, parseo completo | ≥ 1 | ninguna | debe coincidir con la `version` actual del documento | `VALIDACION_ENTRADA` (ausente o malformado) · `VERSION_DESACTUALIZADA` (no coincide) | RNF-003 y concurrencia optimista; derivado |

- El texto corregido **no se escapa al guardarse**. La codificación contra XSS ocurre en el punto de renderizado, según RNF-012: escapar aquí corrompería el contenido del documento.
- Response 200: `{ id, textoCorregido: string, version: number (incrementada), estadoRevision: "EN_REVISION", actualizadoEn: string }`
- Idempotencia: no aplica — enviar el mismo texto dos veces produce el mismo estado final; no hay efecto que duplicar
- Concurrencia: **optimista (`version` en body)**. Motivo: el mismo operador puede tener el documento abierto en dos pestañas o en el celular y la laptop a la vez, y perder una corrección manual de texto es perder exactamente el trabajo que este paso existe para producir. Se usa un campo `version` dedicado y no `updated_at`, que no garantiza cambiar en cada escritura. El `GET` del documento expone `version`. Un desajuste devuelve **409** con `VERSION_DESACTUALIZADA` y el texto vigente en `detalle.textoActual`, para que la persona decida; **nunca** se reintenta la escritura en silencio con la versión recién obtenida.
- Errores: `VALIDACION_ENTRADA`, `VERSION_DESACTUALIZADA`, `RECURSO_NO_ENCONTRADO`, `SESION_CERRADA_NO_MODIFICABLE`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`, solo documentos de sesiones propias
- Versión del contrato: v1

---

## POST /documentos/{id}/marcar
- Ruta real: `POST /documentos/{id}/marcar` — fija el resultado de la revisión (RF-005)
- Path params: `id: number`
- Request: `{ resultado: string (requerido) }`

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `resultado` (body) | enum | requerido, no null, no vacío | uno de `CORRECTA`, `CORREGIDA`, `ILEGIBLE` | conjunto cerrado del ciclo de vida aprobado en RF-005 | ninguna — no se normaliza a mayúsculas: un valor en otra caja se rechaza, para que el conjunto cerrado sea exactamente el aprobado | `CORREGIDA` exige que el texto haya sido editado; `CORRECTA` exige que no lo haya sido | `ESTADO_DOCUMENTO_INVALIDO` (fuera del conjunto) · `TRANSICION_DOCUMENTO_INVALIDA` (transición no permitida desde el estado actual) | RF-005: ciclo de vida |

- Response 200: `{ id, estadoRevision: string, revisadoEn: string, revisadoPor: number, version: number }`
- Idempotencia: no aplica — marcar dos veces el mismo resultado devuelve `TRANSICION_DOCUMENTO_INVALIDA` desde el estado ya terminal, lo que informa que la revisión ya se hizo en vez de fingir éxito
- Concurrencia: no aplica — la transición valida el estado de origen dentro de la misma transacción
- Errores: `ESTADO_DOCUMENTO_INVALIDO`, `TRANSICION_DOCUMENTO_INVALIDA`, `RECURSO_NO_ENCONTRADO`, `SESION_CERRADA_NO_MODIFICABLE`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`, solo documentos de sesiones propias
- Versión del contrato: v1

---

## POST /documentos/{id}/reabrir-revision
- Ruta real: `POST /documentos/{id}/reabrir-revision` — vuelve a `EN_REVISION` desde un estado terminal (RF-005, RF-014)
- Precondición: el documento está en `CORRECTA`, `CORREGIDA` o `ILEGIBLE`, **y** o bien su sesión no está `CERRADA` y es propia del actor, o bien el actor tiene rol `administrador`
- Response 200: `{ id, estadoRevision: "EN_REVISION", revisadoEn: null, version: number }`
- Idempotencia: no aplica — reabrir un documento ya en `EN_REVISION` devuelve `TRANSICION_DOCUMENTO_INVALIDA`
- Concurrencia: no aplica
- Errores: `TRANSICION_DOCUMENTO_INVALIDA`, `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador` (sesión propia no cerrada) o `administrador` (sin restricción)
- Versión del contrato: v1

---

## POST /documentos/{id}/reintentar-ocr
- Ruta real: `POST /documentos/{id}/reintentar-ocr` (RF-004)
- Precondición: el documento está en `PENDIENTE_OCR` y tiene un fallo previo registrado en `LogError`
- Response 202: `{ id, estadoRevision: "PENDIENTE_OCR", encoladoEn: string }`
- Idempotencia: **deduplicación** — si ya hay un job encolado o en ejecución para ese documento, no se encola otro y se devuelve la misma respuesta. Sin esto, un operador impaciente encolaría el mismo trabajo costoso varias veces.
- Concurrencia: no aplica
- Errores: `OCR_YA_PROCESADO`, `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`, solo documentos propios
- Versión del contrato: v1

---

## GET /buscar
- Ruta real: `GET /buscar` — búsqueda por contenido del documento (RF-007)
- Query params:
  - `q: string` (**requerido**)
  - `pacienteId?: number` (opcional) — acota la búsqueda a un paciente
  - `tipo?: string` (opcional) — igualdad sobre el conjunto cerrado de tipos
  - `pagina?`, `porPagina?` — mismas reglas del proyecto
  - `orden?: string` (opcional, default `relevancia:desc`) — permitidos `relevancia:desc`, `fecha_documento:desc`, `fecha_documento:asc`

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `q` (query) | string | requerido; vacío tras normalizar se rechaza | Unicode libre | 2–120 caracteres tras normalizar | trim exterior y colapso de espacios; se pasa a PostgreSQL como **parámetro enlazado** vía `plainto_tsquery('spanish', ?)`, nunca concatenado (RNF-011) | ninguna | `BUSQUEDA_TERMINO_VACIO` · `VALIDACION_ENTRADA` | RF-007; límite inferior propuesto para evitar recorridos inútiles con una sola letra |
| `pacienteId` (query) | identificador entero | opcional | dígitos, parseo completo | ≥ 1 | ninguna | el paciente existe | `PARAMETRO_LISTADO_INVALIDO` · `RECURSO_NO_ENCONTRADO` | RF-007; derivado |
| `tipo` (query) | enum | opcional | conjunto cerrado de tipos de documento | conjunto cerrado | ninguna | ninguna | `PARAMETRO_LISTADO_INVALIDO` | glosario |

- Orden por defecto: `relevancia desc, fecha_documento desc, id desc` — el `id` es el desempate único; sin él, dos documentos con la misma relevancia podrían aparecer o desaparecer entre páginas
- Alcance: se aplica **antes** de calcular `total` y el fragmento. El rol `consulta` solo busca en documentos de sesiones `CERRADA`.
- Documentos `ILEGIBLE`: excluidos, porque su vector de búsqueda está vacío (RF-007)
- Response 200: `{ datos: Resultado[], meta: {...} }` donde `Resultado = { documentoId, paciente: { id, numeroHistoria, apellidos, nombres }, tipo, fechaDocumento, fragmento: string, relevancia: number }`
- El `fragmento` se genera con `ts_headline` sobre el texto ya escapado, y las marcas de resaltado se aplican después del escapado (RNF-012). Nunca se inserta HTML del documento en la vista.
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: `BUSQUEDA_TERMINO_VACIO`, `VALIDACION_ENTRADA`, `PARAMETRO_LISTADO_INVALIDO`, `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador`, `consulta`, `administrador`
- Versión del contrato: v1

---

## GET /documentos/{id}
- Ruta real: `GET /documentos/{id}` (RF-009)
- Path params: `id: number`
- Alcance: rol `consulta` solo si la sesión de origen está `CERRADA`; rol `operador` además sobre sus propias sesiones; `administrador` sin restricción. Fuera de alcance se responde `RECURSO_NO_ENCONTRADO`, no `NO_AUTORIZADO`, para no filtrar la existencia del documento.
- Efecto lateral: registra una fila en `Auditoria` con acción `consultar` (RF-012)
- Response 200: `{ id, paciente: {...}, tipo, fechaDocumento, estadoRevision, textoExtraido: string|null, textoCorregido: string|null, version: number, motorOcr: string|null, urlImagen: string, digitalizadoPor: { id, nombre }, digitalizadoEn: string, sesionId: number, orden: number }`
- Si `estadoRevision` es `ILEGIBLE`, ambos textos vienen en `null` y la vista indica explícitamente que la hoja no tiene texto asociado, en vez de mostrar un panel vacío sin explicación (RF-009)
- `version` se expone aquí porque es el token que consume `PATCH /documentos/{id}/texto`
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador`, `consulta`, `administrador`, con las condiciones de alcance de la matriz
- Versión del contrato: v1

---

## GET /documentos/{id}/imagen
- Ruta real: `GET /documentos/{id}/imagen` — entrega el archivo original (RF-009)
- Alcance: idéntico a `GET /documentos/{id}`. La imagen **nunca** se sirve como archivo estático desde una ruta pública: siempre pasa por esta operación, que evalúa permisos. Guardarla bajo el directorio público del servidor haría inaplicable la matriz de permisos.
- Response 200: el binario original con su `Content-Type` real y `Content-Disposition: inline`
- El archivo devuelto es byte a byte el original almacenado; su SHA-256 coincide con `hash_imagen` (RNF-003)
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — mismas condiciones que ver el documento
- Versión del contrato: v1

---

## GET /ilegibles
- Ruta real: `GET /ilegibles` — cola de hojas por reescanear (RF-014)
- Query params: `pagina?`, `porPagina?`, `orden?` (default `created_at:desc`; permitidos `created_at:desc`, `created_at:asc`)
- Alcance: rol `operador` ve solo las de sus sesiones; `administrador` ve todas. Se aplica antes de calcular `total`.
- Response 200: `{ datos: [{ documentoId, paciente: {...}, sesionId, tipo, fechaDocumento, creadoEn }], meta: {...} }`
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: `PARAMETRO_LISTADO_INVALIDO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador` (propias), `administrador` (todas)
- Versión del contrato: v1

Aprobado por (Arquitectura): pendiente — fecha: pendiente
