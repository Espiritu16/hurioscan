# Requisitos funcionales — HuriosCan

Fuente de negocio: `docs/propuesta/propuesta.md`.
Todos los RF de este documento están en estado `propuesto` hasta que el usuario real los apruebe.

---

## RF-001 — Registro y búsqueda de pacientes
- Descripción: el sistema permite registrar un paciente con los datos de la carátula de su folder físico y buscarlo después por número de historia clínica, DNI o nombre.
- Criterio de aceptación: dado un paciente registrado con N.º HC `0021-4487`, al buscar por ese número, por su DNI o por una parte de su apellido, el sistema lo devuelve; buscar un valor inexistente devuelve una lista vacía, no un error. Registrar un segundo paciente con un N.º HC ya existente se rechaza con `PACIENTE_HC_DUPLICADO`.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-002 — Apertura de sesión de digitalización por folder
- Descripción: el operador abre una sesión de digitalización asociada a un único paciente. El paciente queda fijado para toda la sesión; ninguna hoja capturada dentro de ella puede pertenecer a otro paciente.
- Criterio de aceptación: al abrir una sesión con un paciente seleccionado, toda hoja agregada durante esa sesión queda vinculada a ese paciente sin que el operador tenga que indicarlo por hoja. No se puede abrir una segunda sesión `ABIERTA` para un paciente que ya tiene una: el sistema ofrece retomar la existente y rechaza la creación con `SESION_YA_ABIERTA`.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

### Ciclo de vida de SesionDigitalizacion
- Estado inicial: ABIERTA
- Estados terminales: CERRADA
- Reapertura: no permitida — una hoja que aparezca después se digitaliza en una sesión nueva

| Desde | Acción | Hacia | Actor autorizado | Precondición | Efectos | Error si no aplica |
|---|---|---|---|---|---|---|
| ABIERTA | enviar a revisión | EN_REVISION | operador dueño de la sesión | al menos una hoja capturada | `enviadoARevisionEn` = ahora | TRANSICION_SESION_INVALIDA |
| EN_REVISION | volver a captura | ABIERTA | operador dueño de la sesión | ninguna | `enviadoARevisionEn` = null | TRANSICION_SESION_INVALIDA |
| EN_REVISION | cerrar | CERRADA | operador dueño de la sesión | ninguna hoja de la sesión sigue en `PENDIENTE_OCR` o `EN_REVISION` | `cerradoEn` = ahora; el folder queda contabilizado como digitalizado | TRANSICION_SESION_INVALIDA |

- Aprobado por: pendiente — fecha: pendiente

## RF-003 — Captura de hojas del folder
- Descripción: dentro de una sesión abierta, el operador agrega las hojas del folder por tres vías: tomar foto con la cámara, elegir imágenes de la galería del dispositivo, o subir archivos desde el equipo. Cada hoja se registra con su tipo de documento y su fecha.
- Criterio de aceptación: se pueden agregar varias hojas en una misma sesión, conservando el orden de captura. Cada hoja nueva hereda por defecto el tipo de documento y la fecha de la hoja anterior, y el operador puede cambiarlos. Un archivo que no sea JPEG, PNG, WebP o PDF se rechaza con `HOJA_FORMATO_NO_SOPORTADO` y no se almacena.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-004 — Extracción automática de texto
- Descripción: al registrarse una hoja, el sistema encola su procesamiento y extrae el texto de la imagen mediante un motor de OCR configurable. La imagen original se conserva siempre y nunca se modifica.
- Criterio de aceptación: la imagen original queda almacenada y consultable aunque el OCR falle. El operador puede seguir capturando hojas mientras el OCR de las anteriores todavía se procesa. Si el motor falla, la hoja queda en `PENDIENTE_OCR` con el fallo registrado en `LogError`, y puede reintentarse sin volver a capturarla.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-005 — Revisión y corrección del texto extraído
- Descripción: el operador revisa cada hoja comparando la imagen original con el texto extraído, puede corregir ese texto y marca el resultado de la revisión.
- Criterio de aceptación: el operador puede editar el texto extraído y marcar la hoja como correcta, corregida o ilegible. La imagen original permanece intacta en los tres casos. El texto corregido es el que queda indexado para la búsqueda.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

### Ciclo de vida de Documento (estado de revisión)
- Estado inicial: PENDIENTE_OCR
- Estados terminales: CORRECTA, CORREGIDA, ILEGIBLE
- Reapertura: permitida — una hoja ya revisada puede volver a `EN_REVISION` para corregirla; el estado deja de ser terminal absoluto

| Desde | Acción | Hacia | Actor autorizado | Precondición | Efectos | Error si no aplica |
|---|---|---|---|---|---|---|
| PENDIENTE_OCR | extraer texto | EN_REVISION | operador (automático, vía job) | el motor de OCR devolvió texto | se guarda `textoExtraido`; `ocrProcesadoEn` = ahora | TRANSICION_DOCUMENTO_INVALIDA |
| PENDIENTE_OCR | reintentar OCR | PENDIENTE_OCR | operador | el intento anterior falló | se reencola el job | TRANSICION_DOCUMENTO_INVALIDA |
| EN_REVISION | marcar correcta | CORRECTA | operador | el texto no fue editado | `revisadoEn` = ahora; `revisadoPor` = actor | TRANSICION_DOCUMENTO_INVALIDA |
| EN_REVISION | marcar corregida | CORREGIDA | operador | el texto fue editado | se guarda `textoCorregido`; `revisadoEn` = ahora; `revisadoPor` = actor | TRANSICION_DOCUMENTO_INVALIDA |
| EN_REVISION | marcar ilegible | ILEGIBLE | operador | ninguna | `revisadoEn` = ahora; `revisadoPor` = actor; la hoja no aporta texto a la búsqueda | TRANSICION_DOCUMENTO_INVALIDA |
| CORRECTA, CORREGIDA, ILEGIBLE | reabrir revisión | EN_REVISION | operador | la sesión de origen no está `CERRADA`, o el actor tiene rol administrador | `revisadoEn` = null | TRANSICION_DOCUMENTO_INVALIDA |

- Aprobado por: pendiente — fecha: pendiente

## RF-006 — Cierre de la sesión de digitalización
- Descripción: el operador cierra la sesión tras revisar sus hojas. El sistema muestra un resumen con la cantidad de hojas por estado de revisión y por tipo de documento antes de confirmar.
- Criterio de aceptación: el cierre se rechaza con `SESION_CON_HOJAS_SIN_REVISAR` si alguna hoja sigue en `PENDIENTE_OCR` o `EN_REVISION`. Una hoja `ILEGIBLE` no impide el cierre. Una sesión `CERRADA` no admite agregar ni quitar hojas.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-007 — Búsqueda de documentos por contenido
- Descripción: cualquier usuario autorizado busca documentos por el texto contenido en ellos, además de por paciente. El resultado muestra en qué documentos aparece el término, con un fragmento del texto donde se encuentra.
- Criterio de aceptación: buscar un término presente en el texto de un documento devuelve ese documento con un fragmento que incluye el término resaltado. Los documentos en estado `ILEGIBLE` no aparecen en resultados por contenido, porque no tienen texto asociado. Un término que no aparece en ningún documento devuelve una lista vacía, no un error.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-008 — Línea de tiempo de documentos del paciente
- Descripción: el sistema muestra todos los documentos de un paciente ordenados por la fecha del documento, con su tipo y su estado de revisión, y permite filtrar por tipo de documento y por rango de fechas.
- Criterio de aceptación: los documentos aparecen ordenados por fecha de documento descendente, con `id` como desempate. Al filtrar por un tipo, solo se muestran los documentos de ese tipo y el conteo total refleja el filtro aplicado.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-009 — Visualización de un documento
- Descripción: el usuario abre un documento y ve la imagen original junto al texto extraído, con la ficha del documento: tipo, fecha, estado de revisión, quién lo digitalizó y en qué sesión.
- Criterio de aceptación: la imagen mostrada es el archivo original almacenado, sin modificaciones. Si el documento está `ILEGIBLE`, se muestra la imagen y se indica explícitamente que no tiene texto asociado, en vez de mostrar un panel vacío sin explicación.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-010 — Panel de avance de la campaña
- Descripción: el sistema muestra el avance de la digitalización: folders cerrados, hojas procesadas, hojas ilegibles pendientes y las sesiones más recientes.
- Criterio de aceptación: los conteos se calculan sobre datos reales del sistema, no sobre valores fijos. El total de folders del acervo es un valor configurable del establecimiento, porque el sistema no puede conocer cuántos folders físicos existen.
- Prioridad: media
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-011 — Autenticación y roles
- Descripción: el acceso al sistema requiere autenticación. Cada usuario tiene uno de tres roles: operador de digitalización, consulta o administrador.
- Criterio de aceptación: una solicitud sin credencial válida se rechaza con `NO_AUTENTICADO`. Un usuario con rol `consulta` que intenta abrir una sesión de digitalización se rechaza con `NO_AUTORIZADO`. Toda combinación de actor y operación que no figure en `docs/requisitos/actores-permisos.md` se rechaza.
- Prioridad: alta
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-012 — Auditoría de accesos a documentos
- Descripción: el sistema registra quién consultó qué documento y cuándo, además de las creaciones, modificaciones y eliminaciones de las entidades del dominio. El administrador puede consultar ese registro.
- Criterio de aceptación: abrir un documento genera una fila de auditoría con el usuario, el documento y el instante. El registro no puede editarse ni borrarse desde la aplicación. Un usuario sin rol administrador que intenta consultarlo se rechaza con `NO_AUTORIZADO`.
- Prioridad: media
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-013 — Reanudación de sesiones pendientes
- Descripción: el operador ve las sesiones que dejó sin cerrar y puede retomarlas donde las dejó.
- Criterio de aceptación: una sesión en estado `ABIERTA` o `EN_REVISION` aparece en la lista de pendientes con su paciente, su cantidad de hojas y su fecha de apertura, y al retomarla se recupera con todas sus hojas ya capturadas.
- Prioridad: media
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

## RF-014 — Cola de hojas ilegibles
- Descripción: el sistema lista las hojas marcadas como ilegibles para que puedan volver a escanearse con mejores condiciones.
- Criterio de aceptación: una hoja marcada `ILEGIBLE` aparece en la lista con su paciente y su sesión de origen. Al reabrir su revisión, deja de figurar en la lista.
- Prioridad: baja
- Estado: propuesto
- Aprobado por: pendiente — fecha: pendiente

---

## Fuera del horizonte

Registrado explícitamente para que no se planifique por inercia:

- Extracción de campos estructurados (diagnóstico, medicamentos, dosis) a partir del texto — fase 2 de la propuesta.
- Integración con RENHICE u otros sistemas del MINSA.
- Registro de atenciones nuevas: HuriosCan no es una historia clínica electrónica.
- Aplicación móvil nativa: la captura se hace desde el navegador del dispositivo.
