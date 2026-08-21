# Requisitos no funcionales — HuriosCan

Todos **aprobados** — Kevin, 2026-08-18.

## RNF-001 — rendimiento: búsqueda por contenido
- Requisito: una búsqueda de texto completo sobre el acervo devuelve resultados en menos de 2 segundos con al menos 30 000 documentos indexados.
- Cómo se mide/verifica: seeder que genera 30 000 documentos con texto ficticio; se cronometra la consulta de búsqueda y se inspecciona el plan con `EXPLAIN ANALYZE` confirmando que usa el índice GIN sobre el vector de búsqueda, no un recorrido secuencial.
- Aplica a: la operación de búsqueda por contenido (RF-007).
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-002 — rendimiento: la captura no espera al OCR
- Requisito: registrar una hoja devuelve el control al operador sin esperar el resultado del OCR; el procesamiento ocurre en una cola en segundo plano.
- Cómo se mide/verifica: test que registra una hoja con el motor de OCR simulando una demora de 5 segundos y comprueba que la operación de captura responde en menos de 1 segundo y deja el job encolado.
- Aplica a: captura de hojas (RF-003) y extracción de texto (RF-004).
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-003 — integridad: la imagen original es inmutable
- Requisito: la imagen escaneada se guarda una sola vez y ninguna operación posterior del sistema la modifica, la recomprime ni la reemplaza. El preprocesamiento para OCR produce una copia de trabajo, nunca sobrescribe el original.
- Cómo se mide/verifica: test que registra una hoja, guarda el hash del archivo original, ejecuta OCR y revisión completa, y verifica que el hash no cambió.
- Aplica a: todo el ciclo de vida de un documento.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-004 — usabilidad: una sola interfaz para celular y escritorio
- Requisito: las vistas funcionan sin desplazamiento horizontal ni recortes en anchos de 360, 768, 1024 y 1440 px. La captura y la revisión son operables con el pulgar en 360 px.
- Cómo se mide/verifica: revisión manual de cada vista en los cuatro anchos, registrada en el handoff del sprint que la introduce.
- Aplica a: todas las vistas.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-005 — datos: instantes en UTC
- Requisito: todo instante se almacena y se lee como el mismo momento UTC canónico, usando `timestamptz` de PostgreSQL. La conversión a hora de Perú ocurre solo al mostrar.
- Cómo se mide/verifica: test que crea un registro con la zona de sesión alterada y verifica que el instante recuperado es idéntico al esperado en UTC.
- Aplica a: toda la persistencia. La `fechaDocumento` es una fecha local sin hora (no un instante) y se guarda como `date`, sin conversión de zona.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-006 — trazabilidad: retención de auditoría
- Requisito: las filas de `Auditoria` se conservan durante 5 años desde su creación. La purga posterior es un proceso explícito y programado, nunca un borrado desde el código de aplicación.
- Cómo se mide/verifica: revisión de que la aplicación no tiene privilegio de `UPDATE`/`DELETE` sobre la tabla de auditoría, y de que no existe ninguna ruta de código que los ejecute.
- Aplica a: `Auditoria` y `LogError`.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-010 — seguridad: validación de input
- Requisito: todo input aplica la matriz de validaciones documentada en `docs/contratos/`: se inventaría cada campo, se rechaza explícitamente el formato o tipo inválido, y nunca se modifica en silencio para "limpiarlo".
- Cómo se mide/verifica: test que envía input malformado a cada operación y espera el código de error documentado, no una versión corregida del dato.
- Aplica a: toda entrada que recibe el backend, incluidos archivos subidos y parámetros de búsqueda.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-011 — seguridad: queries parametrizadas
- Requisito: ninguna consulta se arma por concatenación o interpolación de strings con input del usuario, incluida la consulta de búsqueda de texto completo, que recibe el término como parámetro enlazado.
- Cómo se mide/verifica: revisión de código sobre la capa de persistencia buscando concatenación en consultas, más un test que envía un término de búsqueda con sintaxis de inyección y verifica que se trata como texto literal.
- Aplica a: toda la capa de persistencia.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-012 — seguridad: codificación de salida contextual
- Requisito: la codificación que previene XSS ocurre donde el dato se renderiza. El texto extraído por OCR es contenido no confiable —proviene de una imagen arbitraria— y se escapa siempre al mostrarlo en Blade, incluido el fragmento resaltado de los resultados de búsqueda, que se arma escapando primero y resaltando después.
- Cómo se mide/verifica: test que crea un documento cuyo texto extraído contiene un payload XSS y verifica que se muestra escapado en el HTML renderizado, no ejecutado, tanto en el visor como en el fragmento de resultados.
- Aplica a: todas las vistas Blade y componentes Livewire.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-013 — seguridad: control de acceso deny-by-default
- Requisito: una operación sin regla de autorización explícita en `docs/requisitos/actores-permisos.md` rechaza, no permite.
- Cómo se mide/verifica: por cada operación de la matriz, tres pruebas mínimas —sin autenticar, con credencial válida y con credencial inválida o expirada— más una prueba por cada rol adicional con fila propia y dos pruebas por cada fila con condición de alcance (condición cumplida y no cumplida). El acceso a un documento de otro establecimiento o fuera del alcance del actor se rechaza aunque el identificador sea válido.
- Aplica a: toda operación del sistema.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-014 — seguridad: sin secretos ni datos personales expuestos
- Requisito: ningún log, mensaje de error o respuesta expone credenciales, tokens ni datos personales de pacientes. Los mensajes de error del OCR no incluyen el texto extraído ni la imagen.
- Cómo se mide/verifica: revisión de los registros de `LogError` y de las respuestas de error en la auditoría de cierre de cada sprint que toque OCR o documentos.
- Aplica a: todo el sistema.
- Aprobado por: Kevin — fecha: 2026-08-18

## RNF-015 — seguridad: límite de intentos de acceso
- Requisito: `POST /acceder` limita los intentos fallidos consecutivos y rechaza por frecuencia antes de seguir evaluando credenciales. El rechazo por frecuencia **no revela si el correo existe**: se comporta igual para una cuenta real y para una inventada, igual que ya hacen los cuatro modos de fallo de acceso. Un acceso correcto reinicia el contador.
- Cómo se mide/verifica: prueba que agota el límite con credenciales inválidas y comprueba que el intento siguiente se rechaza por frecuencia; que el mismo número de intentos contra un correo inexistente produce **el mismo desenlace**, sin diferencia observable en mensaje, estado ni tiempo de respuesta; que un acceso válido antes de agotar el límite reinicia el contador; y que superado el periodo de bloqueo se vuelve a admitir. La prueba controla el reloj en vez de esperar de verdad, y se demuestra capaz de fallar quitando el límite.
- Aplica a: `POST /acceder`. No aplica al resto de operaciones, que ya están detrás de autenticación.
- Aprobado por: **pendiente** — fecha: pendiente

> **Por qué se propone.** QA verificó el 2026-08-21, al validar B01, que se pueden hacer **12 intentos fallidos consecutivos sin ningún rechazo por frecuencia**. En ese momento se registró como riesgo conocido y no como incumplimiento, y era la lectura correcta: ninguna fuente aprobada lo exigía —ni los RF, ni RNF-010 a RNF-014, ni el contrato de `POST /acceder`—. Este RNF es lo que faltaba para que deje de ser un hueco y pase a ser exigible.
>
> **Dos razones concretas, no una preferencia general.** Primera: agravaba `QA-B01-01` de forma directa, porque cada intento fallido escribía una contraseña en el log, de modo que un atacante podía llenarlo con credenciales ajenas. Ese defecto ya está corregido, así que **esta razón se debilitó** y conviene decirlo en vez de arrastrarla como si siguiera intacta. Segunda, y es la que sigue en pie: **B01 fijó la superficie de autenticación**, así que añadir el límite después es modificar algo ya construido y validado en lugar de diseñarlo.
>
> **Qué queda deliberadamente sin fijar aquí, y por qué.** El número de intentos, la ventana de tiempo y la duración del bloqueo **no** se fijan en el requisito: son parámetros de operación que dependen de cómo trabaje el establecimiento —un operador de archivo que teclea mal tres veces seguidas es normal— y clavarlos en un RNF obliga a reabrirlo para afinarlos. El RFC del sprint que lo implemente los propone con su justificación, y quedan como configuración. Lo que **sí** fija este requisito es lo que no puede negociarse: que exista el límite, que no filtre la existencia de cuentas, y que un acceso correcto reinicie el contador.
>
> **Qué sprint lo implementa.** No B01, que ya cerró. Al tocar `POST /acceder`, que es superficie del dominio Usuarios, lo natural es **B07** —el sprint de gestión de usuarios y auditoría— o un sprint propio si Kevin prefiere no esperar tanto. La decisión es suya y no la toma este documento.
>
> **Aviso de alcance:** el límite por sí solo no protege de un ataque distribuido, y no pretende hacerlo. Cierra el caso de fuerza bruta desde un origen, que es el que QA reprodujo.
