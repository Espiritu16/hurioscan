# Integración frontend — HuriosCan

Autoridad: Arquitectura. Estado: **aprobado** — Arquitectura, 2026-08-19.

**Este proyecto no tiene un cliente HTTP separado.** La UI son componentes Livewire que llaman directamente a los servicios de dominio en el mismo proceso PHP. Por eso "integración" aquí mapea cada flujo de la interfaz a la operación del contrato que ejecuta, no a una llamada de red. Varias filas de la plantilla genérica (base URL, tipos generados, renovación de token, mocks) no aplican y se declaran como tales en vez de omitirse.

| Flujo/ruta UI | Contrato canónico | Cuándo consulta | Auth | Estado asíncrono | Éxito/actualización | Errores→UX | Prueba |
|---|---|---|---|---|---|---|---|
| Acceder `/acceder` | `docs/contratos/usuarios.md` — `POST /acceder`, v1 | al enviar el formulario | `Anónimo` | idle→enviando→éxito/error | redirección al panel del rol | `NO_AUTENTICADO` → mensaje único junto al formulario; `VALIDACION_ENTRADA` → mensaje por campo | Feature test de Livewire: credenciales válidas, inválidas y usuario inactivo |
| Avance `/avance` | `docs/contratos/digitalizacion.md` — `GET /avance`, v1 | al montar el componente | `operador`, `administrador` | carga→éxito | render completo | `NO_AUTORIZADO` → el rol `consulta` no ve la opción y la ruta redirige | Feature test por rol |
| Pacientes `/pacientes` | `docs/contratos/pacientes.md` — `GET /pacientes` y `POST /pacientes`, v1 | al montar y en cada cambio del término, con retraso de 300 ms | `operador`, `consulta`, `administrador` | idle→buscando→vacío/éxito/error | la lista se reemplaza y la página vuelve a 1 al cambiar el término | `PACIENTE_HC_DUPLICADO` → mensaje junto al campo con enlace al paciente existente | Feature test de búsqueda, alta y duplicado |
| Alta con autocompletado `/pacientes` | `docs/contratos/pacientes.md` — `POST /pacientes/consultar-dni`, v1 | al pulsar "Traer datos", nunca automáticamente al tipear | `operador`, `administrador` | idle→consultando→ya registrado/precargado/no encontrado/no disponible | los campos se precargan y quedan editables; el origen se marca `proveedor` y vuelve a `manual` si el operador edita | `IDENTIDAD_NO_ENCONTRADA` → aviso junto al DNI y formulario editable; `IDENTIDAD_PROVEEDOR_NO_DISPONIBLE` → mismo tratamiento, sin exponer el motivo técnico | Feature test de las tres rutas: encontrado, no encontrado y proveedor caído |
| Abrir sesión | `docs/contratos/digitalizacion.md` — `POST /sesiones`, v1 | al confirmar el paciente | `operador` | enviando→éxito/error | redirige a la captura | `SESION_YA_ABIERTA` → enlace directo a `detalle.sesionExistenteId` para retomarla | Feature test de apertura y de sesión duplicada |
| Captura `/sesiones/{id}` | `docs/contratos/digitalizacion.md` — `POST /sesiones/{id}/hojas` y `DELETE .../hojas/{hoja}`, v1 | por cada archivo agregado | `operador`, sesión propia | por hoja: subiendo→éxito/error, con progreso individual | la hoja nueva se agrega a la grilla sin recargar el resto | `HOJA_FORMATO_NO_SOPORTADO` y `HOJA_DEMASIADO_GRANDE` → error en esa tarjeta, las demás hojas se conservan | Feature test de subida válida, formato inválido y archivo excedido |
| Revisión `/sesiones/{id}/revision` | `docs/contratos/documentos.md` — `PATCH /documentos/{id}/texto` y `POST /documentos/{id}/marcar`, v1 | al guardar el texto y al marcar el resultado | `operador`, sesión propia | guardando→éxito/conflicto/error | avanza a la hoja siguiente tras marcar | `VERSION_DESACTUALIZADA` → muestra `detalle.textoActual` y pide decidir; **nunca reenvía con la versión nueva en silencio** | Feature test de corrección, marcado y conflicto de versión |
| Cierre `/sesiones/{id}/cierre` | `docs/contratos/digitalizacion.md` — `POST /sesiones/{id}/cerrar`, v1 | al confirmar | `operador`, sesión propia | confirmando→éxito/error | redirige al avance | `SESION_CON_HOJAS_SIN_REVISAR` → lista las hojas que faltan con enlace a cada una | Feature test de cierre válido y con hojas pendientes |
| Búsqueda `/buscar` | `docs/contratos/documentos.md` — `GET /buscar`, v1 | al enviar el término y al cambiar de página | `operador`, `consulta`, `administrador` | idle→buscando→vacío/éxito/error | reemplaza resultados y conserva los filtros | `BUSQUEDA_TERMINO_VACIO` → mensaje junto al campo | Feature test de coincidencia, sin coincidencias y alcance por rol |
| Documento `/documentos/{id}` | `docs/contratos/documentos.md` — `GET /documentos/{id}` y `GET /documentos/{id}/imagen`, v1 | al montar | los tres roles con su condición de alcance | carga→éxito/error | render completo | `RECURSO_NO_ENCONTRADO` → página de no encontrado, sin distinguir "no existe" de "no autorizado" | Feature test de alcance por rol y estado de sesión |
| Auditoría `/auditoria` | `docs/contratos/usuarios.md` — `GET /auditoria`, v1 | al montar y al cambiar filtros | `administrador` | carga→vacío/éxito | reemplaza la tabla | `PARAMETRO_LISTADO_INVALIDO` → mensaje junto al filtro rechazado | Feature test de acceso por rol y de filtro inválido |

## Cliente compartido

- **Base URL/variables**: no aplica — los componentes Livewire llaman a los servicios de dominio en el mismo proceso; no hay origen HTTP externo que configurar.
- **Tipos**: no aplica — no hay generación de tipos: PHP tipa los servicios de dominio directamente y esos mismos tipos son los que consume la vista.
- **Auth/renovación/cierre**: sesión de servidor de Laravel con cookie `HttpOnly` y `SameSite=Lax`; renovación implícita por actividad; `POST /salir` invalida la sesión y regenera el identificador. No se usan tokens en almacenamiento del navegador.
- **Timeout/cancelación/reintento**: la subida de cada hoja tiene un límite de 60 segundos. Al abandonar la vista, las subidas en curso se cancelan y su resultado se descarta: una respuesta tardía nunca modifica la vista actual. **No hay reintento automático** de ninguna operación con efectos; el reintento es siempre una acción explícita de la persona.
- **Caché/invalidation**: tras cerrar una sesión se recalculan los agregados del avance; tras marcar una hoja se refresca el resumen de la sesión. Livewire vuelve a renderizar el componente afectado, no toda la página.
- **Mocks**: en desarrollo y en tests se configuran dos sustituciones por entorno — el motor de OCR como `nulo` (`OCR_MOTOR=nulo`), que devuelve texto vacío, y el proveedor de identidad como `simulado` (`IDENTIDAD_PROVEEDOR=simulado`), que responde desde una tabla fija de DNIs de ejemplo. Ambas viven en la configuración, nunca en una rama de código dentro del flujo productivo. El proveedor simulado no es opcional: los DNI ficticios no existen en RENIEC, así que sin él las pruebas no tendrían contra qué correr.

## Nombres de ruta canónicos

El menú y los enlaces consumen rutas **por nombre**, nunca por URL literal. El nombre canónico de cada ruta es su path con puntos por segmento estático, sin parámetros: `acceder`, `salir`, `avance`, `pacientes`, `pacientes.detalle` (`/pacientes/{id}`), `sesiones.pendientes`, `sesiones.detalle` (`/sesiones/{id}`), `sesiones.revision`, `sesiones.cierre`, `buscar`, `documentos.detalle` (`/documentos/{id}`), `ilegibles`, `usuarios`, `auditoria`, `componentes` (solo local). El backend debe declarar exactamente estos nombres al implementar las rutas reales. — Fijado por Arquitectura, 2026-08-19.

## Interfaces de servicio

Los componentes Livewire dependen de las interfaces fijadas en `docs/contratos/servicios-aplicacion.md`, nunca de una implementación concreta ni del namespace `Dobles`. Ese documento define también cómo se selecciona el doble por entorno, extendiendo el mecanismo que ADR-0002 estableció para `MotorOcr`.

Estado: aprobado
Aprobado por (Arquitectura): sesión Coordinación+Arquitectura — fecha: 2026-08-19
