# Actores y permisos — HuriosCan

Autoridad: Arquitectura. Estado: `propuesto` hasta aprobación.

El proyecto no expone una API HTTP pública: las operaciones son rutas web y acciones de componentes Livewire. La columna "Recurso/Operación" nombra esa operación real, no un endpoint REST.

## Actor: Operador de digitalización
- Descripción: personal de archivo que escanea los folders físicos y revisa el texto extraído.
- Rol técnico: `operador` (campo `rol` de la entidad `Usuario`)
- Deriva de: RF-002, RF-003, RF-005, RF-006, RF-013, RF-014

## Actor: Personal de consulta
- Descripción: personal clínico y de admisión que busca y lee documentos ya digitalizados. No digitaliza.
- Rol técnico: `consulta` (campo `rol` de la entidad `Usuario`)
- Deriva de: RF-007, RF-008, RF-009

## Actor: Administrador
- Descripción: responsable del sistema en el establecimiento. Gestiona usuarios y revisa la auditoría. **No digitaliza por defecto**: si además debe digitalizar, se le asigna también el rol `operador`, de modo que la auditoría distinga con qué rol actuó.
- Rol técnico: `administrador` (campo `rol` de la entidad `Usuario`)
- Deriva de: RF-010, RF-011, RF-012

## Actor: Anónimo
- Descripción: solicitud sin credencial. Solo alcanza el formulario de acceso.
- Rol técnico: ninguno — ausencia de credencial
- Deriva de: RF-011

## Matriz de permisos

| Actor | Rol técnico | Recurso/Operación | Acción | Condición/alcance | Permitido | Deriva de |
|---|---|---|---|---|---|---|
| Anónimo | (ninguno) | `GET /acceder` · formulario de acceso | ver | — | Sí | RF-011 |
| Anónimo | (ninguno) | `POST /acceder` · autenticar | crear sesión | — | Sí | RF-011 |
| Operador | operador | `POST /salir` · cerrar sesión | eliminar sesión | — | Sí | RF-011 |
| Consulta | consulta | `POST /salir` · cerrar sesión | eliminar sesión | — | Sí | RF-011 |
| Administrador | administrador | `POST /salir` · cerrar sesión | eliminar sesión | — | Sí | RF-011 |
| Operador | operador | `GET /pacientes` · buscar paciente | listar | — | Sí | RF-001 |
| Consulta | consulta | `GET /pacientes` · buscar paciente | listar | — | Sí | RF-001 |
| Administrador | administrador | `GET /pacientes` · buscar paciente | listar | — | Sí | RF-001 |
| Operador | operador | `POST /pacientes` · registrar paciente | crear | — | Sí | RF-001 |
| Consulta | consulta | `POST /pacientes` · registrar paciente | crear | — | No | RF-001 |
| Administrador | administrador | `POST /pacientes` · registrar paciente | crear | — | Sí | RF-001 |
| Operador | operador | `POST /sesiones` · abrir sesión de digitalización | crear | — | Sí | RF-002 |
| Consulta | consulta | `POST /sesiones` · abrir sesión de digitalización | crear | — | No | RF-002, RF-011 |
| Administrador | administrador | `POST /sesiones` · abrir sesión de digitalización | crear | — | No | RF-011 |
| Operador | operador | `GET /sesiones/pendientes` · listar sesiones sin cerrar | listar | solo las propias (`sesion.operador_id == actor.id`) | Sí | RF-013 |
| Administrador | administrador | `GET /sesiones/pendientes` · listar sesiones sin cerrar | listar | — | Sí | RF-010, RF-013 |
| Operador | operador | `GET /sesiones/{id}` · ver sesión | ver | solo las propias (`sesion.operador_id == actor.id`) | Sí | RF-013 |
| Administrador | administrador | `GET /sesiones/{id}` · ver sesión | ver | — | Sí | RF-010 |
| Operador | operador | `POST /sesiones/{id}/hojas` · capturar hoja | crear | sesión propia y en estado `ABIERTA` | Sí | RF-003 |
| Operador | operador | `DELETE /sesiones/{id}/hojas/{hoja}` · quitar hoja | eliminar | sesión propia y en estado `ABIERTA` | Sí | RF-003 |
| Operador | operador | `POST /sesiones/{id}/enviar-a-revision` · transición | actualizar | sesión propia, estado `ABIERTA`, con al menos una hoja | Sí | RF-002 |
| Operador | operador | `POST /sesiones/{id}/volver-a-captura` · transición | actualizar | sesión propia y en estado `EN_REVISION` | Sí | RF-002 |
| Operador | operador | `POST /sesiones/{id}/cerrar` · cerrar folder | actualizar | sesión propia, estado `EN_REVISION`, sin hojas en `PENDIENTE_OCR` ni `EN_REVISION` | Sí | RF-006 |
| Operador | operador | `PATCH /documentos/{id}/texto` · corregir texto extraído | actualizar | documento de una sesión propia no `CERRADA` | Sí | RF-005 |
| Operador | operador | `POST /documentos/{id}/marcar` · marcar resultado de revisión | actualizar | documento de una sesión propia no `CERRADA` | Sí | RF-005 |
| Operador | operador | `POST /documentos/{id}/reabrir-revision` · reabrir | actualizar | documento de una sesión propia no `CERRADA` | Sí | RF-005, RF-014 |
| Administrador | administrador | `POST /documentos/{id}/reabrir-revision` · reabrir | actualizar | — | Sí | RF-005, RF-014 |
| Operador | operador | `POST /documentos/{id}/reintentar-ocr` · reencolar OCR | actualizar | documento propio en `PENDIENTE_OCR` con fallo registrado | Sí | RF-004 |
| Operador | operador | `GET /buscar` · buscar por contenido | listar | — | Sí | RF-007 |
| Consulta | consulta | `GET /buscar` · buscar por contenido | listar | — | Sí | RF-007 |
| Administrador | administrador | `GET /buscar` · buscar por contenido | listar | — | Sí | RF-007 |
| Operador | operador | `GET /pacientes/{id}` · línea de tiempo del paciente | ver | — | Sí | RF-008 |
| Consulta | consulta | `GET /pacientes/{id}` · línea de tiempo del paciente | ver | — | Sí | RF-008 |
| Administrador | administrador | `GET /pacientes/{id}` · línea de tiempo del paciente | ver | — | Sí | RF-008 |
| Operador | operador | `GET /documentos/{id}` · ver documento | ver | documento de una sesión `CERRADA`, o de una sesión propia | Sí | RF-009 |
| Consulta | consulta | `GET /documentos/{id}` · ver documento | ver | documento de una sesión `CERRADA` | Sí | RF-009 |
| Administrador | administrador | `GET /documentos/{id}` · ver documento | ver | — | Sí | RF-009 |
| Operador | operador | `GET /documentos/{id}/imagen` · descargar original | ver | mismo alcance que ver documento | Sí | RF-009 |
| Consulta | consulta | `GET /documentos/{id}/imagen` · descargar original | ver | mismo alcance que ver documento | Sí | RF-009 |
| Administrador | administrador | `GET /documentos/{id}/imagen` · descargar original | ver | — | Sí | RF-009 |
| Operador | operador | `GET /avance` · panel de avance | ver | — | Sí | RF-010 |
| Administrador | administrador | `GET /avance` · panel de avance | ver | — | Sí | RF-010 |
| Consulta | consulta | `GET /avance` · panel de avance | ver | — | No | RF-010 |
| Operador | operador | `GET /ilegibles` · cola de hojas ilegibles | listar | solo las de sesiones propias | Sí | RF-014 |
| Administrador | administrador | `GET /ilegibles` · cola de hojas ilegibles | listar | — | Sí | RF-014 |
| Administrador | administrador | `GET /auditoria` · consultar auditoría | listar | — | Sí | RF-012 |
| Operador | operador | `GET /auditoria` · consultar auditoría | listar | — | No | RF-012 |
| Consulta | consulta | `GET /auditoria` · consultar auditoría | listar | — | No | RF-012 |
| Administrador | administrador | `GET /usuarios` · listar usuarios | listar | — | Sí | RF-011 |
| Administrador | administrador | `POST /usuarios` · crear usuario | crear | — | Sí | RF-011 |
| Administrador | administrador | `PATCH /usuarios/{id}` · cambiar rol o estado | actualizar | no puede quitarse a sí mismo el rol administrador | Sí | RF-011 |

**Cualquier combinación de actor × operación × acción que no aparece en esta tabla, con su condición cumplida, se trata como denegada por defecto.** Esta matriz es la fuente concreta contra la que se verifica RNF-013.

**Precedencia:** primero se filtran las filas que aplican al caso concreto (misma operación, misma acción, la identidad real, condición cumplida); entre las que quedan, gana la denegación.

**Nota sobre alcance de lectura:** un documento solo es visible para el rol `consulta` cuando su sesión de origen está `CERRADA`. Mientras la digitalización de un folder está en curso, su contenido es provisional —el texto todavía no fue revisado— y exponerlo al personal clínico induciría a leer datos sin verificar.

**Nota sobre roles múltiples:** un usuario puede tener más de un rol técnico (por ejemplo `administrador` y `operador`). En ese caso se evalúan juntas todas las filas que aplican a esa identidad y se aplica la precedencia sobre el conjunto, nunca el resultado de un rol aislado.

Aprobado por (Arquitectura): pendiente — fecha: pendiente
