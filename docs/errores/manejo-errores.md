# Manejo de errores — HuriosCan

Autoridad: Arquitectura. Estado: `propuesto` hasta aprobación.

El sistema no expone una API JSON pública: las operaciones son rutas web y acciones Livewire. El **código** es la identidad estable del error y lo que las pruebas verifican; el **resultado HTTP** es cómo se representa en este mecanismo. La UI decide por código, nunca comparando el texto del mensaje.

## Formato estándar

Toda condición de error produce internamente:

```
{ "codigo": string, "mensaje": string, "detalle"?: object }
```

- En una acción Livewire se traduce a un error de validación o a un mensaje de sesión que conserva el `codigo`.
- En una petición web completa se traduce al status HTTP de la tabla.
- El `mensaje` es texto para la persona y puede cambiar sin romper nada; el `codigo` no cambia sin invalidar las pruebas que lo verifican.
- El `mensaje` nunca incluye datos personales del paciente, el texto extraído por OCR, rutas de archivo internas ni fragmentos de SQL (RNF-014).

## Errores de validación de campos

Los errores sintácticos y de campo comparten un código general con detalle por campo, en vez de un código nuevo por cada regla:

```
{ "codigo": "VALIDACION_ENTRADA",
  "mensaje": "Revisa los datos ingresados.",
  "detalle": { "campos": [ { "campo": "numero_historia", "regla": "requerido", "mensaje": "..." } ] } }
```

Resultado HTTP: **422**. Se usa para presencia, tipo, formato, longitud y valores fuera del conjunto permitido. Las reglas de negocio y los conflictos tienen código propio, porque el usuario debe reaccionar distinto.

## Taxonomía

| Código | HTTP status | Cuándo se usa | Deriva de |
|---|---|---|---|
| `VALIDACION_ENTRADA` | 422 | cualquier campo inválido por presencia, tipo, formato o límite | RNF-010 |
| `NO_AUTENTICADO` | 401 | no hay credencial, o es inválida o expiró | RF-011, RNF-013 |
| `NO_AUTORIZADO` | 403 | autenticado, pero la matriz de permisos deniega esa operación | RF-011, RNF-013 |
| `RECURSO_NO_ENCONTRADO` | 404 | el identificador no existe, o existe fuera del alcance del actor | RNF-013 |
| `PACIENTE_HC_DUPLICADO` | 409 | ya existe un paciente con ese número de historia clínica | RF-001 |
| `PACIENTE_DNI_DUPLICADO` | 409 | ya existe un paciente con ese DNI | RF-001 |
| `SESION_YA_ABIERTA` | 409 | el paciente ya tiene una sesión sin cerrar; se ofrece retomarla | RF-002 |
| `TRANSICION_SESION_INVALIDA` | 409 | la transición de estado de la sesión no está permitida desde su estado actual | RF-002 |
| `SESION_SIN_HOJAS` | 409 | se intenta enviar a revisión una sesión que no tiene ninguna hoja | RF-002 |
| `SESION_CON_HOJAS_SIN_REVISAR` | 409 | se intenta cerrar una sesión con hojas en `PENDIENTE_OCR` o `EN_REVISION` | RF-006 |
| `SESION_CERRADA_NO_MODIFICABLE` | 409 | se intenta agregar, quitar o editar una hoja de una sesión ya cerrada | RF-006 |
| `TRANSICION_DOCUMENTO_INVALIDA` | 409 | la transición del estado de revisión no está permitida desde su estado actual | RF-005 |
| `ESTADO_DOCUMENTO_INVALIDO` | 422 | el estado de revisión enviado no pertenece al conjunto cerrado aprobado | RF-005 |
| `HOJA_FORMATO_NO_SOPORTADO` | 422 | el archivo no es JPEG, PNG, WebP ni PDF, verificado por contenido y no solo por extensión | RF-003 |
| `HOJA_DEMASIADO_GRANDE` | 422 | el archivo supera el límite de 15 MB por hoja | RF-003 |
| `OCR_NO_DISPONIBLE` | 503 | el motor configurado no responde o no está instalado; la hoja queda en `PENDIENTE_OCR` y se puede reintentar | RF-004 |
| `OCR_YA_PROCESADO` | 409 | se intenta reintentar el OCR de una hoja que ya salió de `PENDIENTE_OCR` | RF-004 |
| `VERSION_DESACTUALIZADA` | 409 | el `version` enviado no coincide con el vigente: otra escritura modificó el documento primero. `detalle.textoActual` trae el contenido vigente para que la persona decida | RF-005 |
| `BUSQUEDA_TERMINO_VACIO` | 422 | el término de búsqueda quedó vacío tras normalizar | RF-007 |
| `PARAMETRO_LISTADO_INVALIDO` | 422 | filtro, campo de orden o cursor no reconocido en un listado | RF-008, RNF-010 |
| `ADMIN_NO_PUEDE_QUITARSE_ROL` | 409 | un administrador intenta quitarse a sí mismo el rol administrador | RF-011 |

## Distinción que las pruebas deben respetar

- **`NO_AUTENTICADO` vs. `NO_AUTORIZADO`**: no son intercambiables. Una credencial ausente, inválida o expirada es siempre `NO_AUTENTICADO`; nunca se degrada a "tratar como anónimo".
- **`RECURSO_NO_ENCONTRADO` vs. `NO_AUTORIZADO`**: cuando el actor no tiene permiso ni siquiera para saber que el recurso existe (un documento de una sesión ajena todavía abierta), se responde `RECURSO_NO_ENCONTRADO`, no `NO_AUTORIZADO` — distinguirlos ahí filtraría la existencia del recurso.
- **`ESTADO_DOCUMENTO_INVALIDO` vs. `TRANSICION_DOCUMENTO_INVALIDA`**: el primero es un valor que no pertenece al conjunto; el segundo es un valor válido al que no se puede llegar desde el estado actual.

## Códigos que no pertenecen a ninguna operación

`OCR_NO_DISPONIBLE` no aparece en el campo `Errores` de ningún contrato, y es correcto: lo produce el job de extracción de texto en segundo plano, no una operación que un actor invoque. Su comportamiento está documentado en `docs/integraciones/ocr.md`. Se registra aquí para que su ausencia en los contratos no se lea como un olvido.

## Violaciones de restricciones del motor

Una violación de `UNIQUE`, `CHECK` o FK se traduce al código de negocio correspondiente de esta tabla antes de salir de la capa de persistencia. Nunca se expone el nombre de la restricción, el texto del error de PostgreSQL ni el SQL. El caso concreto ya previsto: la violación del índice único parcial de sesión abierta se traduce a `SESION_YA_ABIERTA`.

Aprobado por (Arquitectura): pendiente — fecha: pendiente
