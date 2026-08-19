# Contrato — dominio Digitalización

Autoridad: Arquitectura. Estado: **aprobado** — Kevin (usuario) y Arquitectura, 2026-08-19.
Operaciones servidas por componentes Livewire sobre rutas web; no hay API HTTP pública.

**Paginación:** mismo criterio offset del dominio Pacientes (`pagina` base 1, `porPagina` default 20 máximo 100), por consistencia entre listados del proyecto.
**Campos desconocidos:** se rechazan con `VALIDACION_ENTRADA`.

---

## POST /sesiones
- Ruta real: `POST /sesiones` — abre la sesión de digitalización de un folder (RF-002)
- Request: `{ pacienteId: number (requerido) }`

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `pacienteId` (body) | identificador entero | requerido, no null | dígitos, parseo completo | ≥ 1 | ninguna | el paciente existe y no está eliminado; el paciente no tiene otra sesión en estado distinto de `CERRADA` | `VALIDACION_ENTRADA` · `RECURSO_NO_ENCONTRADO` · `SESION_YA_ABIERTA` | RF-002: una sesión abierta por paciente; derivado: tipo |

- Response 201: `{ id: number, pacienteId: number, operadorId: number, estado: "ABIERTA", creadoEn: string (ISO-8601 UTC) }`
- Response 409 con `SESION_YA_ABIERTA`: `detalle: { sesionExistenteId: number }` — la UI ofrece retomarla en vez de dejar al operador sin salida
- Campos generados por servidor: `id`, `operadorId` (se toma del actor autenticado, **nunca del request**), `estado`, `creadoEn`
- Idempotencia: **deduplicación (unique)** — el índice único parcial sobre `paciente_id WHERE estado <> 'CERRADA'` garantiza a nivel de motor que un doble envío no cree dos sesiones. La violación se traduce a `SESION_YA_ABIERTA`, nunca se expone el error de PostgreSQL.
- Concurrencia: no aplica — creación
- Errores: `VALIDACION_ENTRADA`, `RECURSO_NO_ENCONTRADO`, `SESION_YA_ABIERTA`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`. `consulta` y `administrador` están denegados en la matriz.
- Versión del contrato: v1

---

## GET /sesiones/pendientes
- Ruta real: `GET /sesiones/pendientes` (RF-013)
- Query params: `pagina?`, `porPagina?`, `orden?` (default `created_at:desc`; permitidos `created_at:desc`, `created_at:asc`)
- Orden por defecto: `created_at desc, id desc`
- Alcance: el rol `operador` ve **solo las propias**; `administrador` ve todas. El filtro de alcance se aplica antes de calcular `total`.
- Consistencia temporal: vista viva
- Response 200: `{ datos: Sesion[], meta: {...} }` donde `Sesion = { id, paciente: { id, numeroHistoria, apellidos, nombres }, estado, hojas: number, hojasSinRevisar: number, creadoEn }`
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: `PARAMETRO_LISTADO_INVALIDO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador` (propias), `administrador` (todas)
- Versión del contrato: v1

---

## POST /sesiones/{id}/hojas
- Ruta real: `POST /sesiones/{id}/hojas` — captura una hoja del folder (RF-003)
- Path params: `id: number` — sesión destino
- Request: multipart — `{ archivo: file (requerido), tipo: string (requerido), fechaDocumento?: string (opcional) }`

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `id` (path) | identificador entero | requerido | dígitos, parseo completo | ≥ 1 | ninguna | la sesión existe, pertenece al actor y está en estado `ABIERTA` | `VALIDACION_ENTRADA` · `RECURSO_NO_ENCONTRADO` · `SESION_CERRADA_NO_MODIFICABLE` · `TRANSICION_SESION_INVALIDA` | RF-003; derivado |
| `archivo` (multipart) | archivo binario | requerido | **tipo verificado por contenido**, no por extensión ni por el MIME declarado por el cliente: JPEG, PNG, WebP o PDF | máximo 15 MB | el nombre original **no** se usa como ruta ni como nombre en disco: se almacena con un nombre generado por el servidor | ninguna | `VALIDACION_ENTRADA` · `HOJA_FORMATO_NO_SOPORTADO` · `HOJA_DEMASIADO_GRANDE` | RF-003; límite de 15 MB **propuesto** — deriva del tamaño típico de una foto de celular actual, confirmar contra los equipos reales del establecimiento |
| `tipo` (multipart) | enum | requerido | uno de `hoja_atencion`, `receta`, `laboratorio`, `epicrisis`, `consentimiento`, `otro` | conjunto cerrado | ninguna | ninguna | `VALIDACION_ENTRADA` | glosario: tipo de documento |
| `fechaDocumento` (multipart) | fecha local sin hora | opcional; null permitido — el papel puede no tener fecha legible | `YYYY-MM-DD`, calendario válido | no anterior a 1900-01-01; no posterior a hoy | ninguna — no se convierte a instante | no puede ser futura | `VALIDACION_ENTRADA` | RF-003; rango propuesto |

- Response 201: `{ id: number, orden: number, tipo: string, fechaDocumento: string|null, estadoRevision: "PENDIENTE_OCR", creadoEn: string }`
- El campo `orden` lo asigna el servidor por posición dentro de la sesión; nunca lo envía el cliente.
- La respuesta llega **sin esperar el OCR**: el job queda encolado (RNF-002).
- Idempotencia: **deduplicación (unique)** — se calcula el SHA-256 del archivo y se rechaza con `VALIDACION_ENTRADA` (`detalle.regla = "hoja_duplicada_en_sesion"`) si esa misma imagen ya está en la sesión. Motivo: en una captura por celular con conexión intermitente, el reintento del operador es el caso normal, y una hoja duplicada ensucia el conteo del folder y el resumen de cierre. No es `requerida` porque no hace falta reproducir la respuesta original.
- Concurrencia: no aplica — creación
- Errores: `VALIDACION_ENTRADA`, `HOJA_FORMATO_NO_SOPORTADO`, `HOJA_DEMASIADO_GRANDE`, `RECURSO_NO_ENCONTRADO`, `SESION_CERRADA_NO_MODIFICABLE`, `TRANSICION_SESION_INVALIDA`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`, solo sobre sesiones propias
- Versión del contrato: v1

---

## DELETE /sesiones/{id}/hojas/{hoja}
- Ruta real: `DELETE /sesiones/{id}/hojas/{hoja}` — quita una hoja capturada por error (RF-003)
- Path params: `id: number` (sesión), `hoja: number` (documento)
- Precondición: la sesión es propia y está en estado `ABIERTA`; la hoja pertenece a esa sesión
- Efecto: soft-delete del documento (`deleted_at`) y reordenamiento del campo `orden` de las hojas siguientes. La imagen original **no** se borra del disco: el registro conserva su trazabilidad (RNF-003).
- Response 204: sin cuerpo
- Idempotencia: no aplica — una segunda llamada sobre una hoja ya eliminada devuelve `RECURSO_NO_ENCONTRADO`
- Concurrencia: no aplica
- Errores: `RECURSO_NO_ENCONTRADO`, `SESION_CERRADA_NO_MODIFICABLE`, `TRANSICION_SESION_INVALIDA`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`, solo sesiones propias
- Versión del contrato: v1

---

## POST /sesiones/{id}/enviar-a-revision
- Ruta real: `POST /sesiones/{id}/enviar-a-revision` — transición `ABIERTA → EN_REVISION` (RF-002)
- Path params: `id: number`
- Request: sin cuerpo
- Precondición: sesión propia, estado `ABIERTA`, al menos una hoja no eliminada
- Response 200: `{ id: number, estado: "EN_REVISION", enviadoARevisionEn: string (ISO-8601 UTC) }`
- Idempotencia: no aplica — repetir la acción sobre una sesión ya en `EN_REVISION` devuelve `TRANSICION_SESION_INVALIDA`, que es el comportamiento correcto: informa que el estado ya cambió en vez de fingir éxito
- Concurrencia: no aplica — la transición valida el estado de origen dentro de la misma transacción, lo que ya serializa dos intentos concurrentes
- Errores: `TRANSICION_SESION_INVALIDA`, `SESION_SIN_HOJAS`, `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`, solo sesiones propias
- Versión del contrato: v1

---

## POST /sesiones/{id}/volver-a-captura
- Ruta real: `POST /sesiones/{id}/volver-a-captura` — transición `EN_REVISION → ABIERTA` (RF-002)
- Precondición: sesión propia, estado `EN_REVISION`
- Response 200: `{ id: number, estado: "ABIERTA", enviadoARevisionEn: null }`
- Idempotencia / Concurrencia: mismo criterio que `enviar-a-revision`
- Errores: `TRANSICION_SESION_INVALIDA`, `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`, solo sesiones propias
- Versión del contrato: v1

---

## POST /sesiones/{id}/cerrar
- Ruta real: `POST /sesiones/{id}/cerrar` — transición `EN_REVISION → CERRADA` (RF-006)
- Path params: `id: number`
- Request: sin cuerpo
- Precondición: sesión propia, estado `EN_REVISION`, **ninguna** hoja en `PENDIENTE_OCR` ni en `EN_REVISION`. Una hoja `ILEGIBLE` no impide el cierre.
- Response 200: `{ id, estado: "CERRADA", cerradoEn: string, resumen: { hojas: number, correctas: number, corregidas: number, ilegibles: number, porTipo: { <tipo>: number } } }`
- Efecto: los documentos de la sesión pasan a ser visibles para el rol `consulta`, según la matriz de permisos
- Idempotencia: no aplica — cerrar una sesión ya cerrada devuelve `TRANSICION_SESION_INVALIDA`
- Concurrencia: no aplica — la verificación del estado de origen y de las hojas pendientes ocurre dentro de la misma transacción que el cierre; un `SELECT` previo fuera de transacción no bastaría
- Errores: `TRANSICION_SESION_INVALIDA`, `SESION_CON_HOJAS_SIN_REVISAR`, `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `operador`, solo sesiones propias
- Versión del contrato: v1

Aprobado por (Arquitectura): pendiente — fecha: pendiente

---

## GET /avance
- Ruta real: `GET /avance` — panel de avance de la campaña (RF-010)
- Query params: ninguno — **`Query params: no aplica`**: la respuesta es un conjunto fijo de agregados más las 10 sesiones más recientes, una cota positiva y estructural, no una colección que crezca
- Response 200:
  `{ foldersCerrados: number, totalFoldersAcervo: number|null, porcentaje: number|null, hojasProcesadas: number, hojasIlegibles: number, ritmoSemanal: number, sesionesRecientes: [{ id, paciente: { numeroHistoria, apellidos, nombres }, hojas: number, operador: string, estado: string, creadoEn }] }`
- `totalFoldersAcervo` es `null` mientras el establecimiento no lo haya configurado. En ese caso `porcentaje` también es `null` y la vista muestra el avance absoluto sin porcentaje, en vez de inventar un denominador (RF-010).
- `ritmoSemanal` es la cantidad de sesiones cerradas en los últimos 7 días, calculada sobre datos reales.
- Alcance: el rol `operador` ve los agregados globales de la campaña y solo sus propias sesiones recientes; `administrador` ve todas. El filtro se aplica antes de armar `sesionesRecientes`.
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — roles `operador`, `administrador`. El rol `consulta` está denegado en la matriz.
- Versión del contrato: v1
