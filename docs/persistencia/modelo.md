# Modelo de persistencia — HuriosCan

Motor: PostgreSQL 18.3. ORM: Eloquent (Laravel 13). Mecanismo de migraciones: nativo de Laravel (`database/migrations/`).
Autoridad: Arquitectura. Estado: `propuesto` hasta aprobación.

## Convenciones

- **Instantes en UTC**: todo instante usa `timestamptz`, que PostgreSQL ofrece de forma nativa. La conversión a hora de Perú ocurre solo al mostrar (RNF-005).
- **`fechaDocumento` no es un instante**: es la fecha impresa o escrita en el papel, sin hora ni zona. Se guarda como `date` y no se convierte.
- **Campos base**: Eloquent espera `created_at`, `updated_at` y `deleted_at` en snake_case para que su manejo automático de timestamps y soft-delete funcione. Se respetan esos nombres en lugar del default en español de la guía, tal como la propia guía indica para frameworks con convención automática.
- **Nombres**: entidad en singular y tabla en plural, ambas en español. `SesionDigitalizacion` declara su tabla explícitamente (`sesiones_digitalizacion`) porque el pluralizador de Eloquent, que trabaja en inglés, produciría un nombre incorrecto.
- **Enums**: se persisten como `varchar` con restricción `CHECK` sobre el conjunto cerrado aprobado en el RF correspondiente. No se usa el tipo `enum` nativo de PostgreSQL, cuya alteración requiere migración de tipo.

---

## Entidad: Usuario (tabla: `usuarios`) — deriva de RF-011
- Campos: `id` (PK, bigint), `nombre` (varchar 120, NOT NULL), `email` (varchar 180, NOT NULL, UNIQUE), `password` (varchar 255, NOT NULL — hash bcrypt, nunca el valor en claro), `rol` (varchar 20, NOT NULL, CHECK IN `operador`, `consulta`, `administrador`), `activo` (boolean, NOT NULL, default true), `created_at` / `updated_at` (timestamptz), `deleted_at` (timestamptz, nullable — soft-delete: tiene FK entrantes desde `sesiones_digitalizacion` y valor de auditoría)
- Relaciones: 1:N con `SesionDigitalizacion`; 1:N con `Documento` (como revisor)
- Índices: `email` (unique), `rol`
- Nota sobre roles múltiples: la matriz de permisos contempla que un usuario tenga más de un rol. En el horizonte planificado el campo `rol` admite uno solo; habilitar varios roles por usuario exige una tabla `usuario_rol` y es un cambio de schema que reabre este documento. Se registra explícitamente para no descubrirlo durante la implementación.
- Plan/migraciones relacionadas: MIG-001

## Entidad: Paciente (tabla: `pacientes`) — deriva de RF-001, RF-002
- Campos: `id` (PK, bigint), `numero_historia` (varchar 30, NOT NULL, UNIQUE — cadena de texto, no número: admite ceros iniciales y separadores), `dni` (varchar 12, nullable, UNIQUE cuando no es null — puede faltar en folders antiguos), `apellidos` (varchar 120, NOT NULL), `nombres` (varchar 120, NOT NULL), `fecha_nacimiento` (date, nullable), `origen_datos` (varchar 12, NOT NULL, default `manual`, CHECK IN `manual`, `proveedor` — de dónde salieron los nombres y apellidos, para poder auditar discrepancias entre el archivo físico y la fuente de identidad), `datos_consultados_en` (timestamptz, nullable — cuándo se consultó al proveedor; null si la carga fue manual), `created_at` / `updated_at` (timestamptz), `deleted_at` (timestamptz, nullable — soft-delete: tiene FK entrantes y valor clínico permanente)
- Relaciones: 1:N con `SesionDigitalizacion`; 1:N con `Documento`
- Índices: `numero_historia` (unique), `dni` (unique parcial `WHERE dni IS NOT NULL`), índice de texto sobre `apellidos || ' ' || nombres` para la búsqueda por nombre de RF-001
- **Datos deliberadamente no almacenados**: el proveedor de identidad puede devolver dirección y ubigeo. No se guardan ni se solicitan: ningún requisito del sistema los necesita y almacenar datos personales que nadie usa es recolección innecesaria bajo la Ley N.º 29733. Ver `docs/integraciones/json-pe.md`.
- Plan/migraciones relacionadas: MIG-002

## Entidad: SesionDigitalizacion (tabla: `sesiones_digitalizacion`) — deriva de RF-002, RF-006, RF-013
- Campos: `id` (PK, bigint), `paciente_id` (FK → `pacientes.id`, NOT NULL, ON DELETE RESTRICT), `operador_id` (FK → `usuarios.id`, NOT NULL, ON DELETE RESTRICT), `estado` (varchar 20, NOT NULL, default `ABIERTA`, CHECK IN `ABIERTA`, `EN_REVISION`, `CERRADA`), `enviado_a_revision_en` (timestamptz, nullable), `cerrado_en` (timestamptz, nullable), `created_at` / `updated_at` (timestamptz), `deleted_at` (timestamptz, nullable — soft-delete: tiene FK entrantes desde `documentos`)
- Relaciones: N:1 con `Paciente`; N:1 con `Usuario` (operador); 1:N con `Documento`
- Índices: `paciente_id`, `operador_id`, `estado`; **índice único parcial sobre `paciente_id` WHERE `estado` <> 'CERRADA' AND `deleted_at` IS NULL** — garantiza a nivel de motor la regla de RF-002 de una sola sesión abierta por paciente
- Invariante por ausencia bajo concurrencia: la regla "un paciente no puede tener dos sesiones sin cerrar" no puede garantizarse consultando antes de insertar, porque desde el caso vacío no hay fila que serialice dos inserciones concurrentes. El índice único parcial anterior es la restricción nativa que sí lo garantiza; la aplicación traduce su violación al código `SESION_YA_ABIERTA`, nunca expone el error del motor. Verificación mínima: dos aperturas concurrentes para el mismo paciente, una sola confirma.
- Plan/migraciones relacionadas: MIG-003

## Entidad: Documento (tabla: `documentos`) — deriva de RF-003, RF-004, RF-005, RF-007, RF-008, RF-009, RF-014
- Campos: `id` (PK, bigint), `paciente_id` (FK → `pacientes.id`, NOT NULL, ON DELETE RESTRICT — desnormalizado desde la sesión para que la consulta por paciente no exija un join adicional), `sesion_id` (FK → `sesiones_digitalizacion.id`, NOT NULL, ON DELETE RESTRICT), `orden` (smallint, NOT NULL — posición de la hoja dentro del folder), `tipo` (varchar 30, NOT NULL, CHECK IN `hoja_atencion`, `receta`, `laboratorio`, `epicrisis`, `consentimiento`, `otro`), `fecha_documento` (date, nullable — la fecha escrita en el papel; nullable porque un documento deteriorado puede no tenerla legible), `ruta_imagen` (varchar 255, NOT NULL — ruta en el disco configurado, nunca la imagen en la base), `hash_imagen` (char 64, NOT NULL — SHA-256 del original, para verificar la inmutabilidad exigida por RNF-003), `mime` (varchar 40, NOT NULL), `bytes` (integer, NOT NULL), `estado_revision` (varchar 20, NOT NULL, default `PENDIENTE_OCR`, CHECK IN `PENDIENTE_OCR`, `EN_REVISION`, `CORRECTA`, `CORREGIDA`, `ILEGIBLE`), `texto_extraido` (text, nullable — salida cruda del OCR, se conserva aunque se corrija), `texto_corregido` (text, nullable — el que se indexa), `motor_ocr` (varchar 30, nullable — qué motor produjo el texto, para poder comparar precisión entre motores), `ocr_procesado_en` (timestamptz, nullable), `revisado_por` (FK → `usuarios.id`, nullable, ON DELETE RESTRICT), `revisado_en` (timestamptz, nullable), `version` (integer, NOT NULL, default 1 — token de concurrencia optimista dedicado, se incrementa en cada escritura del documento; deliberadamente no se usa `updated_at`, que no garantiza cambiar en cada operación), `busqueda` (tsvector, generada — ver abajo), `created_at` / `updated_at` (timestamptz), `deleted_at` (timestamptz, nullable — soft-delete: el documento es el registro que el sistema existe para conservar)
- Columna generada de búsqueda: `busqueda` se define como columna generada `to_tsvector('spanish', coalesce(texto_corregido, texto_extraido, ''))`. Es derivada, no editable: mantenerla como columna generada evita que un `UPDATE` olvide sincronizarla.
- Relaciones: N:1 con `Paciente`; N:1 con `SesionDigitalizacion`; N:1 con `Usuario` (revisor)
- Índices: `paciente_id`, `sesion_id`, `estado_revision`, `tipo`, `(paciente_id, fecha_documento DESC, id DESC)` para la línea de tiempo de RF-008 con desempate único, **índice GIN sobre `busqueda`** para RNF-001
- Nota: un documento en `ILEGIBLE` tiene `busqueda` vacío y por eso no aparece en resultados por contenido, tal como exige el criterio de RF-007. La imagen sigue siendo consultable.
- Plan/migraciones relacionadas: MIG-004, MIG-005

## Entidad: ConfiguracionEstablecimiento (tabla: `configuracion_establecimiento`) — deriva de RF-010
- Campos: `id` (PK, bigint), `nombre` (varchar 160, NOT NULL), `total_folders_acervo` (integer, nullable — cantidad estimada de folders físicos; el sistema no puede conocerla y sin ella el panel muestra el avance absoluto sin porcentaje), `created_at` / `updated_at` (timestamptz)
- Relaciones: ninguna
- Índices: ninguno — fila única
- Soft-delete: no aplica; es configuración, no un registro histórico
- Plan/migraciones relacionadas: MIG-006

## Entidad: Auditoria (tabla: `auditorias`) — política de documentacion-estructura
- Append-only durante su retención de 5 años (RNF-006). La aplicación se conecta con un rol de base de datos que tiene `INSERT` y `SELECT` sobre esta tabla, pero no `UPDATE` ni `DELETE`. La purga posterior es un proceso programado aparte, nunca código de aplicación.
- Campos: `id` (PK, bigint), `entidad` (varchar 40, NOT NULL — ej. `Documento`), `entidad_id` (bigint, NOT NULL — todas las entidades auditadas usan PK bigint), `accion` (varchar 20, NOT NULL, CHECK IN `crear`, `actualizar`, `eliminar`, `consultar`), `usuario_id` (bigint, nullable — **sin FK real hacia `usuarios`**, referencia lógica documentada: una FK propagaría cambios hacia una tabla que debe ser inmutable), `origen` (varchar 40, NOT NULL, default `usuario` — valores: `usuario`, `job:<nombre>`, `sistema`; identifica al actor no humano cuando `usuario_id` es null), `fecha` (timestamptz, NOT NULL), `valores_anteriores` (jsonb, nullable), `valores_nuevos` (jsonb, nullable)
- **Redacción por allowlist**: nunca se serializa la entidad completa. Se auditan solo los campos declarados por entidad: de `Documento` — `tipo`, `fecha_documento`, `estado_revision`, `sesion_id`, `paciente_id`; de `SesionDigitalizacion` — `estado`, `paciente_id`, `operador_id`; de `Paciente` — `numero_historia`, `apellidos`, `nombres`; de `Usuario` — `rol`, `activo`, `email`. **Nunca** `password`, `texto_extraido`, `texto_corregido` ni `ruta_imagen`. La lista es allowlist para que un campo sensible nuevo no quede expuesto por omisión.
- La acción `consultar` cubre la apertura de un documento exigida por RF-012; se registra solo para `Documento`, no para cada listado, para que la tabla siga siendo utilizable.
- Índices: `(entidad, entidad_id)`, `fecha`, `usuario_id`
- Acceso restringido: solo el rol `administrador` puede consultarla (RF-012)
- Plan/migraciones relacionadas: MIG-007

## Entidad: LogError (tabla: `log_errores`) — política de documentacion-estructura
- Campos: `id` (PK, bigint), `mensaje` (text, NOT NULL), `stack_trace` (text, nullable), `contexto` (jsonb, nullable), `severidad` (varchar 12, NOT NULL, CHECK IN `info`, `warning`, `error`, `critical`), `fecha` (timestamptz, NOT NULL), `resuelto` (boolean, NOT NULL, default false), `trace_id` (uuid, NOT NULL)
- `resuelto` significa en este proyecto: **la causa fue corregida y el cambio está en `main`**, no solo que alguien lo miró. Solo el rol `administrador` puede marcarlo.
- **Allowlist en `contexto`**: solo `operacion`, `documento_id`, `sesion_id`, `usuario_id`, `motor_ocr`. Nunca el objeto de la petición completo, ni headers, ni el texto extraído por OCR (RNF-014).
- **Saneamiento en `mensaje` y `stack_trace`**: antes de guardar se enmascaran patrones de credenciales (claves de API, tokens, cadenas de conexión con contraseña). Un stack trace no se considera seguro por venir del framework.
- **Canal independiente**: además de esta tabla, todo error se escribe como log estructurado a `stderr` por el canal de Laravel, porque si la causa es la propia base de datos la fila puede no llegar a escribirse. Pendiente de verificar en el entorno real que esa salida se recolecta; mientras no se verifique, la tabla es el único registro confiable y así se declara.
- Acceso restringido y retención: mismos que `Auditoria` (RNF-006), porque `contexto` incluye `usuario_id` y por tanto dato personal.
- Índices: `fecha`, `severidad`, `trace_id`
- Plan/migraciones relacionadas: MIG-008

---

## Plan de migraciones

| ID lógico | Orden/depende de | Cambio de schema | Datos/backfill | Reversibilidad | Artefacto esperado | Estado/fuente |
|---|---|---|---|---|---|---|
| MIG-001 | primera | agregar `rol`, `activo` y `deleted_at` a la tabla `users` del scaffold, renombrada a `usuarios` | no aplica — proyecto nuevo | rollback de tabla sin datos productivos | migración nativa de Laravel; ruta pendiente hasta generarla | derivado de RF-011; propuesto |
| MIG-002 | tras MIG-001 | crear `pacientes` con constraints e índices, incluido el único parcial de `dni`, y los campos `origen_datos` y `datos_consultados_en` | no aplica | rollback de tabla vacía | migración nativa; ruta pendiente | derivado de RF-001; propuesto |
| MIG-003 | tras MIG-002 | crear `sesiones_digitalizacion` con FK, CHECK de estado y el índice único parcial de sesión abierta | no aplica | rollback de tabla vacía | migración nativa; ruta pendiente | derivado de RF-002; propuesto |
| MIG-004 | tras MIG-003 | crear `documentos` con FK, CHECK de tipo y estado, columna `version` con default 1, e índices de línea de tiempo | no aplica | rollback de tabla vacía | migración nativa; ruta pendiente | derivado de RF-003, RF-005; propuesto |
| MIG-005 | tras MIG-004 | agregar la columna generada `busqueda` (tsvector, configuración `spanish`) y su índice GIN | no aplica | rollback de columna e índice | migración nativa con SQL crudo — Laravel no expone columnas generadas tsvector en su API fluida | derivado de RF-007, RNF-001; propuesto |
| MIG-006 | tras MIG-001 | crear `configuracion_establecimiento` e insertar la fila única | seeder con el nombre del establecimiento | rollback de tabla vacía | migración nativa + seeder; ruta pendiente | derivado de RF-010; propuesto |
| MIG-007 | tras MIG-004 | crear `auditorias` sin FK hacia `usuarios`, con índices | no aplica | rollback de tabla vacía | migración nativa; ruta pendiente | política de documentacion-estructura; propuesto |
| MIG-008 | tras MIG-001 | crear `log_errores` con índices | no aplica | rollback de tabla vacía | migración nativa; ruta pendiente | política de documentacion-estructura; propuesto |
| MIG-009 | tras MIG-007 | crear el rol de base de datos de la aplicación y revocar `UPDATE`/`DELETE` sobre `auditorias` | no aplica | revertir los `GRANT` | migración nativa con SQL crudo | derivado de RNF-006; propuesto |

MIG-009 es la que hace verificable la inmutabilidad de `Auditoria`: sin ella, la restricción sería solo una convención que cualquier línea de código puede romper.

Aprobado por (Arquitectura): pendiente — fecha: pendiente
