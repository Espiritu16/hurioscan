# Contrato — dominio Usuarios

Autoridad: Arquitectura. Estado: `propuesto` hasta aprobación.

---

## GET /acceder
- Ruta real: `GET /acceder` — formulario de acceso (RF-011)
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: ninguno propio
- Autenticación: **no requerida** — actor `Anónimo` en la matriz de permisos
- Versión del contrato: v1

## POST /acceder
- Ruta real: `POST /acceder` — autentica y crea la sesión de usuario (RF-011)
- Request: `{ email: string (requerido), password: string (requerido), recordar?: boolean (opcional, default false) }`

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `email` (body) | string | requerido, no null, no vacío | forma de email razonable, sin regex excesivamente restrictiva | máximo 180 caracteres | trim exterior; comparación insensible a mayúsculas | el usuario existe y tiene `activo = true` | `VALIDACION_ENTRADA` · `NO_AUTENTICADO` | RF-011; derivado |
| `password` (body) | string (secreto) | requerido, no null, no vacío | Unicode libre; no se restringe el conjunto de caracteres | 8–200 caracteres | **ninguna** — no se hace trim: un espacio puede ser parte de la contraseña | ninguna | `VALIDACION_ENTRADA` · `NO_AUTENTICADO` | RF-011; longitud mínima **propuesta** |
| `recordar` (body) | boolean | opcional, default false | solo `true`/`false` reales; un string ambiguo como `"false"` **no** se interpreta como verdadero por ser no vacío | no aplica | ninguna | ninguna | `VALIDACION_ENTRADA` | derivado |

- **Credenciales inválidas y usuario inexistente devuelven el mismo `NO_AUTENTICADO` con el mismo mensaje**, para no revelar qué correos están registrados. Un usuario con `activo = false` también recibe `NO_AUTENTICADO`, no un mensaje propio.
- La contraseña nunca aparece en logs, mensajes de error ni en `Auditoria` (RNF-014).
- Response 302: redirección al panel correspondiente al rol
- Idempotencia: no aplica
- Concurrencia: no aplica
- Errores: `VALIDACION_ENTRADA`, `NO_AUTENTICADO`
- Autenticación: no requerida — actor `Anónimo`
- Versión del contrato: v1

## POST /salir
- Ruta real: `POST /salir` — cierra la sesión de usuario (RF-011)
- Request: sin cuerpo
- Response 302: redirección al formulario de acceso
- Idempotencia: no aplica — sin sesión activa la operación redirige igual, sin error
- Concurrencia: no aplica
- Errores: ninguno propio
- Autenticación: requerida — roles `operador`, `consulta`, `administrador`
- Versión del contrato: v1

---

## GET /usuarios
- Ruta real: `GET /usuarios` (RF-011)
- Query params: `pagina?`, `porPagina?`, `orden?` (default `nombre:asc`; permitidos `nombre:asc`, `nombre:desc`, `created_at:desc`)
- Orden por defecto: `nombre asc, id asc`
- Response 200: `{ datos: [{ id, nombre, email, rol, activo, creadoEn }], meta: {...} }` — **nunca** incluye el hash de contraseña
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: `PARAMETRO_LISTADO_INVALIDO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `administrador`
- Versión del contrato: v1

## POST /usuarios
- Ruta real: `POST /usuarios` (RF-011)
- Request: `{ nombre: string (requerido), email: string (requerido), password: string (requerido), rol: string (requerido) }`

### Validaciones de entrada
| Campo/ubicación | Tipo semántico | Presencia/default | Formato/caracteres | Límites | Normalización/coerción | Regla cruzada/negocio | Error | Fuente/estado |
|---|---|---|---|---|---|---|---|---|
| `nombre` (body) | string (texto humano) | requerido, no null, no vacío tras trim | Unicode; tildes, apóstrofes y guiones permitidos | 2–120 caracteres | trim exterior; colapso de espacios | ninguna | `VALIDACION_ENTRADA` | RF-011; límite propuesto |
| `email` (body) | string | requerido, no null, no vacío | forma de email razonable | máximo 180 caracteres | trim exterior; se guarda en minúsculas | único entre usuarios no eliminados | `VALIDACION_ENTRADA` | RF-011; derivado |
| `password` (body) | string (secreto) | requerido, no null | Unicode libre | 8–200 caracteres | ninguna — sin trim | ninguna | `VALIDACION_ENTRADA` | RF-011; longitud mínima propuesta |
| `rol` (body) | enum | requerido | uno de `operador`, `consulta`, `administrador` | conjunto cerrado | ninguna | ninguna | `VALIDACION_ENTRADA` | actores-permisos.md |

- La contraseña se almacena solo como hash bcrypt; el valor en claro no se persiste, no se registra ni se devuelve.
- Response 201: `{ id, nombre, email, rol, activo: true, creadoEn }`
- Idempotencia: **deduplicación (unique)** — la restricción `UNIQUE` sobre `email` impide crear el mismo usuario dos veces
- Concurrencia: no aplica — creación
- Errores: `VALIDACION_ENTRADA`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `administrador`
- Versión del contrato: v1

## PATCH /usuarios/{id}
- Ruta real: `PATCH /usuarios/{id}` — cambia rol o estado de actividad (RF-011)
- Path params: `id: number`
- Request: `{ rol?: string (opcional), activo?: boolean (opcional) }`
- **Semántica de `PATCH`**: un campo ausente no cambia; un campo con `null` se rechaza con `VALIDACION_ENTRADA` — ninguno de los dos campos admite null. Enviar el objeto vacío también se rechaza: no hay nada que actualizar.
- Regla cruzada: un administrador **no puede** quitarse a sí mismo el rol `administrador` ni desactivarse a sí mismo — de lo contrario el sistema podría quedar sin ningún administrador activo. Error: `ADMIN_NO_PUEDE_QUITARSE_ROL`.
- Response 200: `{ id, nombre, email, rol, activo, actualizadoEn }`
- Idempotencia: no aplica — asignar el rol que ya tiene produce el mismo estado final, sin efecto que duplicar
- Concurrencia: no aplica — el conjunto de campos editables es pequeño y la última escritura es la vigente; perder una de dos ediciones concurrentes de rol no destruye trabajo manual acumulado, a diferencia del texto de un documento
- Errores: `VALIDACION_ENTRADA`, `ADMIN_NO_PUEDE_QUITARSE_ROL`, `RECURSO_NO_ENCONTRADO`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `administrador`
- Versión del contrato: v1

---

## GET /auditoria
- Ruta real: `GET /auditoria` — consulta del registro de auditoría (RF-012)
- Query params:
  - `entidad?: string` (opcional) — igualdad; conjunto cerrado `Paciente`, `SesionDigitalizacion`, `Documento`, `Usuario`
  - `accion?: string` (opcional) — igualdad; conjunto cerrado `crear`, `actualizar`, `eliminar`, `consultar`
  - `usuarioId?: number` (opcional) — igualdad. Semántica de null: `usuarioId=sistema` devuelve las filas donde `usuario_id` es null (acciones de jobs o del sistema)
  - `desde?` / `hasta?` (opcionales) — rango sobre `fecha`, formato `YYYY-MM-DD` interpretado como día completo en hora de Perú y convertido a instantes UTC para la consulta
  - `pagina?`, `porPagina?` — mismas reglas del proyecto
- Orden por defecto: `fecha desc, id desc` — con desempate único
- Consistencia temporal: vista viva. La tabla es append-only, así que las filas ya vistas no cambian; solo pueden aparecer filas nuevas al principio.
- **Nota de rendimiento**: con la retención de 5 años de RNF-006 esta tabla es la que más crece. Los índices `(entidad, entidad_id)`, `fecha` y `usuario_id` cubren los filtros ofrecidos, pero la paginación offset se degrada en páginas muy profundas. Se acepta porque el uso real es filtrado por fecha o entidad, no recorrido completo. Si en operación se observa degradación, migrar a cursor es un cambio breaking que exige subir la versión del contrato; queda registrado aquí para no descubrirlo como sorpresa.
- Response 200: `{ datos: [{ id, entidad, entidadId, accion, usuario: { id, nombre }|null, origen, fecha, valoresAnteriores: object|null, valoresNuevos: object|null }], meta: {...} }`
- `valoresAnteriores` y `valoresNuevos` contienen solo los campos de la allowlist declarada en `docs/persistencia/modelo.md`; nunca el objeto completo
- Idempotencia / Concurrencia: no aplica — solo lectura
- Errores: `PARAMETRO_LISTADO_INVALIDO`, `VALIDACION_ENTRADA`, `NO_AUTENTICADO`, `NO_AUTORIZADO`
- Autenticación: requerida — rol `administrador`. `operador` y `consulta` están denegados en la matriz.
- Versión del contrato: v1

Aprobado por (Arquitectura): pendiente — fecha: pendiente
