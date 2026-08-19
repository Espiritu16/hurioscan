# Requisitos no funcionales — HuriosCan

Todos en estado `propuesto` hasta aprobación del usuario real.

## RNF-001 — rendimiento: búsqueda por contenido
- Requisito: una búsqueda de texto completo sobre el acervo devuelve resultados en menos de 2 segundos con al menos 30 000 documentos indexados.
- Cómo se mide/verifica: seeder que genera 30 000 documentos con texto ficticio; se cronometra la consulta de búsqueda y se inspecciona el plan con `EXPLAIN ANALYZE` confirmando que usa el índice GIN sobre el vector de búsqueda, no un recorrido secuencial.
- Aplica a: la operación de búsqueda por contenido (RF-007).
- Aprobado por: pendiente — fecha: pendiente

## RNF-002 — rendimiento: la captura no espera al OCR
- Requisito: registrar una hoja devuelve el control al operador sin esperar el resultado del OCR; el procesamiento ocurre en una cola en segundo plano.
- Cómo se mide/verifica: test que registra una hoja con el motor de OCR simulando una demora de 5 segundos y comprueba que la operación de captura responde en menos de 1 segundo y deja el job encolado.
- Aplica a: captura de hojas (RF-003) y extracción de texto (RF-004).
- Aprobado por: pendiente — fecha: pendiente

## RNF-003 — integridad: la imagen original es inmutable
- Requisito: la imagen escaneada se guarda una sola vez y ninguna operación posterior del sistema la modifica, la recomprime ni la reemplaza. El preprocesamiento para OCR produce una copia de trabajo, nunca sobrescribe el original.
- Cómo se mide/verifica: test que registra una hoja, guarda el hash del archivo original, ejecuta OCR y revisión completa, y verifica que el hash no cambió.
- Aplica a: todo el ciclo de vida de un documento.
- Aprobado por: pendiente — fecha: pendiente

## RNF-004 — usabilidad: una sola interfaz para celular y escritorio
- Requisito: las vistas funcionan sin desplazamiento horizontal ni recortes en anchos de 360, 768, 1024 y 1440 px. La captura y la revisión son operables con el pulgar en 360 px.
- Cómo se mide/verifica: revisión manual de cada vista en los cuatro anchos, registrada en el handoff del sprint que la introduce.
- Aplica a: todas las vistas.
- Aprobado por: pendiente — fecha: pendiente

## RNF-005 — datos: instantes en UTC
- Requisito: todo instante se almacena y se lee como el mismo momento UTC canónico, usando `timestamptz` de PostgreSQL. La conversión a hora de Perú ocurre solo al mostrar.
- Cómo se mide/verifica: test que crea un registro con la zona de sesión alterada y verifica que el instante recuperado es idéntico al esperado en UTC.
- Aplica a: toda la persistencia. La `fechaDocumento` es una fecha local sin hora (no un instante) y se guarda como `date`, sin conversión de zona.
- Aprobado por: pendiente — fecha: pendiente

## RNF-006 — trazabilidad: retención de auditoría
- Requisito: las filas de `Auditoria` se conservan durante 5 años desde su creación. La purga posterior es un proceso explícito y programado, nunca un borrado desde el código de aplicación.
- Cómo se mide/verifica: revisión de que la aplicación no tiene privilegio de `UPDATE`/`DELETE` sobre la tabla de auditoría, y de que no existe ninguna ruta de código que los ejecute.
- Aplica a: `Auditoria` y `LogError`.
- Aprobado por: pendiente — fecha: pendiente

## RNF-010 — seguridad: validación de input
- Requisito: todo input aplica la matriz de validaciones documentada en `docs/contratos/`: se inventaría cada campo, se rechaza explícitamente el formato o tipo inválido, y nunca se modifica en silencio para "limpiarlo".
- Cómo se mide/verifica: test que envía input malformado a cada operación y espera el código de error documentado, no una versión corregida del dato.
- Aplica a: toda entrada que recibe el backend, incluidos archivos subidos y parámetros de búsqueda.
- Aprobado por: pendiente — fecha: pendiente

## RNF-011 — seguridad: queries parametrizadas
- Requisito: ninguna consulta se arma por concatenación o interpolación de strings con input del usuario, incluida la consulta de búsqueda de texto completo, que recibe el término como parámetro enlazado.
- Cómo se mide/verifica: revisión de código sobre la capa de persistencia buscando concatenación en consultas, más un test que envía un término de búsqueda con sintaxis de inyección y verifica que se trata como texto literal.
- Aplica a: toda la capa de persistencia.
- Aprobado por: pendiente — fecha: pendiente

## RNF-012 — seguridad: codificación de salida contextual
- Requisito: la codificación que previene XSS ocurre donde el dato se renderiza. El texto extraído por OCR es contenido no confiable —proviene de una imagen arbitraria— y se escapa siempre al mostrarlo en Blade, incluido el fragmento resaltado de los resultados de búsqueda, que se arma escapando primero y resaltando después.
- Cómo se mide/verifica: test que crea un documento cuyo texto extraído contiene un payload XSS y verifica que se muestra escapado en el HTML renderizado, no ejecutado, tanto en el visor como en el fragmento de resultados.
- Aplica a: todas las vistas Blade y componentes Livewire.
- Aprobado por: pendiente — fecha: pendiente

## RNF-013 — seguridad: control de acceso deny-by-default
- Requisito: una operación sin regla de autorización explícita en `docs/requisitos/actores-permisos.md` rechaza, no permite.
- Cómo se mide/verifica: por cada operación de la matriz, tres pruebas mínimas —sin autenticar, con credencial válida y con credencial inválida o expirada— más una prueba por cada rol adicional con fila propia y dos pruebas por cada fila con condición de alcance (condición cumplida y no cumplida). El acceso a un documento de otro establecimiento o fuera del alcance del actor se rechaza aunque el identificador sea válido.
- Aplica a: toda operación del sistema.
- Aprobado por: pendiente — fecha: pendiente

## RNF-014 — seguridad: sin secretos ni datos personales expuestos
- Requisito: ningún log, mensaje de error o respuesta expone credenciales, tokens ni datos personales de pacientes. Los mensajes de error del OCR no incluyen el texto extraído ni la imagen.
- Cómo se mide/verifica: revisión de los registros de `LogError` y de las respuestas de error en la auditoría de cierre de cada sprint que toque OCR o documentos.
- Aplica a: todo el sistema.
- Aprobado por: pendiente — fecha: pendiente
