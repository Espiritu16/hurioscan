---
project: HuriosCan
source_status: CANONICA
baseline: línea base parcialmente aprobada — frontend habilitado; persistencia y ADR pendientes a propósito
active_phase: cadena F00→F07
active_status: EN_VALIDACION
last_completed_phase: D01
bootstrap_status: COMPLETO
planning_horizon_status: PARCIAL — frontend en EN_VALIDACION; backend con línea base aprobada pero sin habilitar
current_rfc_batch: [D01, F00, B01, F01, B02, F02, B03, F03, B04, F05, B05, F06, B06, F07, B07]
planning_scope: [RF-001, RF-002, RF-003, RF-004, RF-005, RF-006, RF-007, RF-008, RF-009, RF-010, RF-011, RF-012, RF-013, RF-014, RF-015]
updated_at: 2026-08-19
repositories:
  - name: hurioscan
    path: ~/hurioscan
    branch: develop
    current_sha: null
sprints:
  - id: D01
    repository: hurioscan
    planning_status: LISTO
    execution_status: COMPLETADO
    depends_on: []
    parallelizable_with: [F00, B01]
  - id: F00
    repository: hurioscan
    planning_status: LISTO
    execution_status: EN_VALIDACION
    depends_on: []
    parallelizable_with: [B01]
  - id: B01
    repository: hurioscan
    planning_status: BORRADOR
    execution_status: PLANIFICADO
    depends_on: []
    parallelizable_with: [F00]
  - id: F01
    repository: hurioscan
    planning_status: LISTO
    execution_status: EN_VALIDACION
    depends_on: [F00]
    parallelizable_with: [B01, B02]
  - id: B02
    repository: hurioscan
    planning_status: BORRADOR
    execution_status: PLANIFICADO
    depends_on: [B01]
    parallelizable_with: [F01, F02]
  - id: F02
    repository: hurioscan
    planning_status: LISTO
    execution_status: EN_VALIDACION
    depends_on: [F00, F01]
    parallelizable_with: [B02, B03]
  - id: B03
    repository: hurioscan
    planning_status: BORRADOR
    execution_status: PLANIFICADO
    depends_on: [B02]
    parallelizable_with: [F02, F03]
  - id: F03
    repository: hurioscan
    planning_status: LISTO
    execution_status: EN_VALIDACION
    depends_on: [F00, F01]
    parallelizable_with: [B03, B04]
  - id: B04
    repository: hurioscan
    planning_status: BORRADOR
    execution_status: PLANIFICADO
    depends_on: [B03]
    parallelizable_with: [F03, F05]
  - id: F05
    repository: hurioscan
    planning_status: LISTO
    execution_status: EN_VALIDACION
    depends_on: [F00, F01, F03]
    parallelizable_with: [B04, B05]
  - id: B05
    repository: hurioscan
    planning_status: BORRADOR
    execution_status: PLANIFICADO
    depends_on: [B04]
    parallelizable_with: [F05, F06]
  - id: F06
    repository: hurioscan
    planning_status: LISTO
    execution_status: EN_VALIDACION
    depends_on: [F00, F01]
    parallelizable_with: [B05, B06, F07]
  - id: B06
    repository: hurioscan
    planning_status: BORRADOR
    execution_status: PLANIFICADO
    depends_on: [B05]
    parallelizable_with: [F06, F07]
  - id: F07
    repository: hurioscan
    planning_status: LISTO
    execution_status: EN_VALIDACION
    depends_on: [F00, F01]
    parallelizable_with: [B06, B07, F06]
  - id: B07
    repository: hurioscan
    planning_status: BORRADOR
    execution_status: PLANIFICADO
    depends_on: [B05]
    parallelizable_with: [F07]
---

# Estado del proyecto

## Progreso
- Proyecto Laravel 13.26.1 creado y verificado: lint, tests y build ejecutan correctamente.
- Estructura domain-first materializada en `app/Dominios/` y `app/Compartido/Ocr/`.
- Documentación inicial completa escrita: requisitos, permisos, glosario, contratos de los cuatro dominios, modelo de persistencia con plan de migraciones, taxonomía de errores, experiencia e integración de interfaz, integración del motor de OCR y cinco ADR.
- Roadmap de quince sprints —siete de backend, siete de frontend y uno de DevOps— con matriz de cobertura de todos los RF y RNF del horizonte, paralelismo declarado por pares y puntos de integración explícitos.
- 2026-08-18: despachados en paralelo F00 (chat FRONTEND) y D01 (chat DEVOP) desde `develop @ bb7ae5b`; chat QA abierto en espera. Decisión de alcance aprobada por Kevin: F00 tokeniza **solo el tema claro** del diseño; el tema oscuro queda fuera del horizonte y requeriría trabajo planificado aparte. Fuente de la paleta: `docs/frontend/diseno/hurioscan-claude-design.html` (ningún ADR contiene colores; se corrigió la referencia del README de diseño).
- 2026-08-19: **aprobación parcial de la línea base por Kevin** — los 15 RF, `actores-permisos.md`, `glosario.md`, `manejo-errores.md` y los 4 contratos (`pacientes`, `digitalizacion`, `documentos`, `usuarios`). Arquitectura aprobó además `docs/frontend/integracion.md` y fijó allí los nombres de ruta canónicos. Los seis RFC de frontend (F01–F07) quedaron aprobados por Kevin y sus sprints en `Planificación: LISTO`. **Persistencia (`docs/persistencia/modelo.md`) y los ADR quedan sin aprobar a propósito: la línea backend sigue bloqueada; ningún sprint B se habilita.**
- 2026-08-19: **D01 en `EN_VALIDACION`** — `sprint/D01 @ 9e9a839`, PR #18 hacia `develop`. Evidencia real en Actions, toda en verde: flujo develop sobre PR #18 (run 32217867989), rojo provocado y descartado vía PR borrador #19 (run 32217969927, cerrado sin fusionar), reuso de caché verificado por rerun, y flujo de main corrido de verdad vía PR borrador #20 (run 32218140280, cerrado sin fusionar). Riesgo residual anotado: acciones de terceros advertidas por Node 20 deprecado; decidir upgrade en mantenimiento futuro. Al cerrar D01, el Coordinador debe reemplazar los `previsto en D01` de `AGENTS.md` (acción de gobernanza aparte).
- 2026-08-19: **F00 avanzado** — UT-01 a UT-04 completas y la vista de UT-05, en `sprint/F00 @ 6024ee8`; pint passed, 19/19 tests, build ok, responsive verificado en 360/768/1024/1440. Falta materializar la ruta `/componentes` y cerrar el sprint.
- 2026-08-19: **D01 COMPLETADO** — QA emitió `APROBADO` sobre `9e9a839` con validación independiente (worktree propio desechable, verificación de las corridas reales vía `gh`, reproducción local de los tres comandos, y `seguridad-validacion` sin hallazgos bloqueantes). PR #18 fusionado a `develop` con autorización de Kevin. Evidencia y observaciones en `docs/handoffs/D01.md`. Se reemplazaron los cuatro `previsto en D01` de `AGENTS.md` por la referencia a los flujos reales — completar valores `previsto` dentro del alcance ya aprobado no invalida su aprobación.
- 2026-08-19: **Arquitectura fijó las interfaces de servicio de aplicación** en `docs/contratos/servicios-aplicacion.md`: cuatro interfaces (una por dominio) en `app/Dominios/<Dominio>/Contratos/`, con selección de doble por entorno vía `config/dobles.php` y `DoblesServiceProvider`. Resuelve un hueco real de la gobernanza: `AGENTS.md` daba a Arquitectura la autoridad sobre las interfaces pero ninguna ruta de código para materializarlas, y la línea frontend no podía cumplir a la vez «el doble se activa solo por configuración» y «ningún código de producción referencia el namespace `Dobles`». Las firmas obligan a B01–B07: se implementan, no se redefinen.

## Auditoría del Coordinador sobre la cadena `F` — 2026-08-19

La cadena `F00`→`F07` quedó construida y en verde en `sprint/F00`, pero la auditoría previa a integrarla encontró dos defectos que **ninguna prueba podía detectar**, porque ambos solo se manifiestan al usar las piezas como se usarán de verdad. Se registran aquí porque la lección vale más que la corrección puntual.

1. **Faltaba el layout que Livewire exige al servir un componente como página.** Livewire 4 resuelve el espacio de nombres `layouts::app` sobre `resources/views/layouts/`; sin ese archivo, montar cualquier ruta falla con «No hint path defined for [layouts]». Las pruebas pasaban porque `Livewire::test()` renderiza sin layout, así que el fallo solo aparecía al montar las rutas. Resuelto con `resources/views/layouts/app.blade.php`, que delega en el `<x-layout>` de F00. **Nota:** el Coordinador diagnosticó mal la ubicación (`components.layouts.app`, que es la convención de otra versión) y la línea frontend lo corrigió reproduciendo el error real en vez de aplicar la indicación recibida. Queda registrado el diagnóstico correcto para que no haya que volver a depurarlo.
2. **Divergencia de contrato en `hojasDeSesion`.** `RevisionOcr` (producción) invocaba un método que solo existía en el doble, no en la interfaz `ServicioDocumentos`. PHP no lo detecta estáticamente y las pruebas tampoco, porque siempre corren contra el doble: habría fallado en runtime al llegar B05. La causa de fondo era un hueco de la línea base — ninguna operación declaraba cómo listar las hojas de una sesión para revisarlas. Arquitectura declaró `GET /sesiones/{id}/hojas` en `docs/contratos/documentos.md` y la sumó a la interfaz.

Al corregir esos dos aparecieron cuatro más, todos de la misma familia. La lista completa, porque el patrón importa más que cada caso:

3. **Un método público de más en un doble**, fuera de su interfaz y sin invocar. No era un fallo latente, pero el backend podía leerlo como parte del contrato. Lo encontró la línea frontend con una comparación por reflexión entre cada doble y su interfaz — mejor que la comparación por `grep` que se había declarado, porque ve también lo que nadie invoca. `servicios-aplicacion.md` adoptó ese criterio.
4. **La forma de la respuesta divergía del contrato**: el doble devolvía la lista pelada donde el contrato la envuelve en `datos`. Componente y doble estaban de acuerdo entre sí y en desacuerdo con el contrato — el modo de fallo que ninguna prueba puede detectar desde adentro.
5. **El manifiesto de Vite en CI.** La prueba nueva que sirve las páginas como páginas reales exige `public/build/manifest.json`, y el workflow corre los tests antes del build. Pasaba localmente porque el entorno tenía un artefacto que el runner no tiene, y `public/build/` no se versiona. Lo detectó el pipeline de D01 en su primer uso real sobre código de aplicación.
6. **Una prueba que dependía de una ausencia.** Verificaba que el menú deshabilita las opciones sin ruta declarada, apoyándose en que ninguna estuviera montada. Al montarlas dejó de tener premisa. Se rehízo para que fabrique su propia condición, y se le agregaron el caso mixto —enlazadas y deshabilitadas conviviendo, que es el estado real hasta B01— y un invariante que no depende del montaje.

**El patrón, que es la conclusión que vale conservar:** los seis eran invisibles desde adentro de la suite porque la pieza no se estaba ejerciendo como se usa de verdad, o porque la prueba se apoyaba en una condición del entorno en vez de fabricarla. Los dos remedios que quedaron en el repositorio son `tests/Feature/Frontend/PaginaRealTest.php`, que sirve los quince componentes como páginas reales con ruta y layout, y la comprobación por reflexión de `servicios-aplicacion.md`. Al despachar QA conviene decirlo explícitamente: **validar sobre vistas montadas, no sobre componentes en aislamiento.**

## Bloqueantes
- ~~**Persistencia y ADR sin aprobar**~~ → **APROBADOS por Kevin el 2026-08-19.** El modelo con sus nueve migraciones y los cinco ADR quedaron firmes.
- **La línea backend sigue sin habilitarse, por decisión de Kevin.** Aprobar la línea base era condición necesaria pero no suficiente: **ningún RFC de sprint `B` está aprobado**, empezando por el de B01, así que B01 permanece en `BORRADOR`/`PLANIFICADO` y no se despacha. Kevin decidió cerrar primero la línea frontend y no avanzar con backend por ahora. Cuando quiera arrancar, el único paso que falta es aprobar el RFC de B01: sus fuentes —RF-011, RNF-005, RNF-013, actores y permisos, el modelo y la taxonomía de errores— ya están todas aprobadas.
- Decisiones abiertas registradas, ninguna bloquea lo aprobado pero sí la implementación del sprint que las consume:
  - formato de documento de identidad: si el establecimiento registra carné de extranjería además de DNI, el formato de 8 dígitos no alcanza. El proveedor elegido ofrece consulta de carné de extranjería como servicio aparte, pero se declaró fuera del horizonte: un paciente extranjero se registra a mano (B02);
  - activación de la cuenta de JSON.pe: los 100 créditos gratuitos vencen a los 30 días y el proyecto dura 12 semanas, así que la cuenta real se activa cerca de la demostración final, no ahora (B02);
  - límite de 15 MB por hoja: confirmar contra los equipos reales del establecimiento (B03);
  - motor de OCR de producción: lo decide el benchmark (B04).

## Siguiente fase habilitada
- **Línea frontend en cadena:** F00 (en curso) y F01, F02, F03, F05, F06, F07 (`Planificación: LISTO`), ejecutados secuencialmente en `sprint/F00` por la sesión FRONTEND. Cada sprint cierra con su commit y su handoff antes de pasar al siguiente; la cadena termina en `EN_VALIDACION` y QA la valida después.
- **D01:** `COMPLETADO` e integrado. Todo PR hacia `develop` ejecuta ahora lint, tests y build automáticamente.
- **Backend:** bloqueado hasta que Kevin apruebe `docs/persistencia/modelo.md` y los ADR.

## Revisión de cobertura ficticia — informe, 2026-08-19

QA revisó **192 aserciones sobre literales** en `develop @ 4ae1d2b` y encontró **tres alarmas apagadas**, cada una demostrada por mutación. Ningún defecto de producto, tal como se había anticipado al aprobar la unidad.

**1 · Prioridad alta — la prueba escrita para impedir QA-F-01 no lo atraparía.** QA borró la ruta `pacientes.alta`, lo que reproduce el defecto entero, y la suite dio **134 pasaron, 0 fallaron**. La causa son tres condicionales de `AccionesConDestinoTest` que apagan la guarda justo cuando la ruta falta: un `markTestSkipped` en el proveedor de datos y dos `if (Route::has(...))` que hacen correr las aserciones solo si la ruta ya existe. El botón tampoco delata nada, porque degrada a deshabilitado —su diseño correcto— y la comprobación acepta `disabled` como destino válido, también correcto. **Cada pieza está bien por separado; juntas dejan pasar el defecto que la prueba existe para impedir.** Los condicionales se escribieron cuando las rutas todavía no existían, para no romper el CI: cumplieron su función y quedaron. Un `skip` sirve para algo que aún no existe; para algo que ya existe y podría desaparecer, la ausencia tiene que fallar.

**2 · Prioridad media — las guardas de jerga del proveedor no atrapan la fuga real.** `assertDontSee('token')` y `assertDontSee('crédito')` vigilan dos palabras que solo existen en **comentarios de PHP**, así que ningún camino de código puede emitirlas. QA filtró el mensaje de la excepción a la vista conservando la frase esperada —para aislar cuál aserción falla— y el test pasó igual: la fuga realista no la ve nadie.

**3 · Prioridad baja — la auditoría se verifica contra el desplegable de filtros.** Los valores que la prueba busca no vienen de las filas sino de los `<option>` del filtro, que están siempre. QA vació las celdas de la tabla y el test pasó. La mitad negativa de esa prueba es sólida; la positiva no verifica lo que dice.

**Lo revisado y sano, registrado para que nadie repita el trabajo:** las cuatro guardas de `role="alert"` pasan el segundo argumento que evita el escapado; las de credenciales sí disparan, verificado inyectando un hash real; la de autorización usa un prefijo en vez de la palabra completa, lo que cubre variantes; y la hipótesis sistémica de que las 141 `assertSee` pudieran pasar por el snapshot de Livewire quedó **refutada** —Livewire lo descarta en `assertSee`—, de modo que solo el patrón `->html()` conserva ese riesgo y se revisó caso por caso.

**Alcance declarado por QA, sin inflarlo:** examinó una por una las 38 aserciones negativas y las 10 positivas sobre `->html()`, que son las de mayor riesgo estructural. Para las 141 `assertSee` probó la hipótesis sistémica y, refutada, hizo un barrido heurístico de los 116 literales únicos. Puede quedar alguna que pase por el motivo equivocado en un caso que el heurístico no vea; esa capa sería otra tanda y con rendimiento esperado bastante menor.

Las tres correcciones están despachadas a la línea frontend. No requieren tocar código de producción.

## Aprobación de la línea base de backend — 2026-08-19

Kevin aprobó `docs/persistencia/modelo.md` y los cinco ADR. Con eso queda cerrada la línea base documental completa del proyecto y **la línea backend deja de estar bloqueada**.

**Dos puntos de producto que se aprobaron explícitamente y conviene no redescubrir:**

1. **Un usuario tiene un solo rol.** La matriz de permisos contemplaba varios, pero el schema admite uno. Habilitar varios exige una tabla `usuario_rol` y reabre el modelo. Si en el establecimiento alguien resulta ser operador y administrador a la vez, es un cambio de schema, no de configuración.
2. **El panel de avance necesita `total_folders_acervo`,** un dato que el sistema no puede averiguar. Sin él muestra el avance absoluto sin porcentaje, que es el comportamiento ya validado en F07.

**Estado de las autorizaciones temporales: siguen vigentes.** AUT-01, AUT-02 y AUT-03 se declararon vigentes «hasta habilitarse B01», y B01 **no** se habilitó. Aprobar el modelo y los ADR no las vence: el disparador es el despacho del sprint. Mientras tanto la línea frontend conserva su perímetro ampliado sobre `routes/web.php`, las interfaces de dominio y `config/livewire.php`.

**Qué hereda B01 al arrancar**, ya registrado en su contexto: la deuda de verificar los propios límites de subida al arrancar y fallar de forma visible; la deuda de fondo de QA-F-04 —que un aviso del motor pueda romper una respuesta JSON en cualquier punto—; y la idea de que `composer dev` delegue en el script de arranque, que es ruta suya.

## Autorizaciones vigentes

Ampliaciones puntuales del perímetro de la línea frontend, aprobadas por Kevin el 2026-08-19 para que la cadena `F` pueda ejecutarse sin su backend. **No modifican `AGENTS.md`:** son excepciones acotadas y con vencimiento, no un cambio de la política de permisos.

### AUT-01 — rutas web (aprobada por Kevin, 2026-08-19)

- La línea frontend puede **agregar** rutas nombradas en `routes/web.php` apuntando únicamente a sus propios componentes o vistas. Nunca modificar ni borrar rutas existentes.
- Los nombres son los canónicos de `docs/frontend/integracion.md` § Nombres de ruta canónicos; ninguno se inventa.
- Cada ruta se agrega **cuando su componente ya existe**, para que el pipeline de CI nunca quede en rojo por una clase inexistente.
- Vence al habilitarse B01, momento en que `routes/web.php` vuelve a su dueño único (línea backend) según `AGENTS.md`.

### AUT-03 — configuración del límite de subida (aprobada por Kevin, 2026-08-19)

Para corregir **QA-F-03**, cuyo arreglo cruza tres capas y ninguna está en las rutas de la línea frontend:

- La línea frontend puede crear y mantener **`config/livewire.php`**, exclusivamente para alinear el límite de subida temporal (`temporary_file_upload.rules`) y sus mensajes con el límite que declara el producto. No habilita ninguna otra escritura en `config/`.
- **DevOps** se encarga de la capa de entorno: `upload_max_filesize` y `post_max_size` de PHP en local y en CI, y del servidor web cuando exista. Es su rutas ya autorizadas más la configuración operativa que `AGENTS.md` le reconoce.
- **Límite del producto: 15 MB**, el valor que ya declaran el contrato y el mensaje al usuario. Sigue siendo una decisión abierta pendiente de contrastar con los equipos reales del establecimiento; cambiarlo después será tocar una línea por capa, y por eso no bloqueó la corrección.

**Corrección del criterio (Arquitectura, 2026-08-19):** esta autorización decía originalmente «configurar 15 MB en `config/livewire.php`», y eso **no habría cerrado el defecto** — solo habría movido el umbral. Lo detectó la línea frontend al implementarla, y el principio que se deduce quedó fijado como criterio del proyecto:

> **El límite del producto lo aplica la única capa que puede explicarlo hoja por hoja —el servicio de dominio—, y toda capa por debajo debe permitir MÁS que él, nunca lo mismo.** Livewire, PHP y el servidor web trabajan sobre la petición completa: cuando una de ellas corta, descarta el lote entero y devuelve un error crudo del framework, que es exactamente el defecto QA-F-03. Una capa inferior configurada con el mismo número que el producto lo reintroduce.

El criterio evitó reintroducir el defecto una segunda vez: el despacho a DevOps decía «alinear PHP con 15 MB», lo que habría matado en PHP las hojas de 16 MB que el dominio debe rechazar con su mensaje. Se corrigió antes de aplicarse.
- **Criterio de cierre del defecto, no negociable:** una hoja rechazada por tamaño muestra su motivo en su tarjeta y **las demás hojas del mismo lote se conservan**, igual que ya ocurre con el rechazo por formato. El mensaje es el del producto, en español, no texto crudo del framework.
- Vence al cerrarse QA-F-03. `config/livewire.php` queda después bajo el dueño que `AGENTS.md` declara para `config/`.

### Estado de las tres capas al 2026-08-19 — coherentes, verificado por Coordinación

| Capa | Valor | Rol |
|---|---|---|
| Servicio de dominio | 15 MB | **límite del producto**: rechaza hoja por hoja, con su mensaje en español, conservando el resto del lote |
| Subida (`config/livewire.php`) | 50 MB | techo contra abuso, comentado como tal para que nadie lo «corrija» al valor del contrato |
| Entorno (`scripts/php/hurioscan.ini`) | `upload_max_filesize=50M`, `post_max_size=60M`, `max_file_uploads=200` | techo duro de recursos; `post` sobre `upload` porque una captura envía varias hojas en la misma petición |

`max_file_uploads` se elevó de su valor por defecto de 20 tras medir que, con 25 hojas, PHP entrega 20 y **descarta 5 dejando el aviso solo en el log del servidor**: la aplicación no puede saber que faltaban. Es la misma familia de pérdida silenciosa que QA-F-03, sin siquiera un mensaje. Ningún requisito fija un máximo de hojas por folder, así que 20 no era una regla de negocio.

La capa de entorno tiene la **primera evidencia de punta a punta del proyecto** en ese nivel: con la configuración anterior una hoja de 13 MB devolvía HTTP 413 con `$_FILES` vacío; con la nueva, hojas de 13, 16 y 30 MB atraviesan PHP y llegan al enrutador. Es reproducción, no lectura de configuración. Para el futuro servidor web, `client_max_body_size` (nginx) o `LimitRequestBody` (Apache) deben alinearse con `post_max_size`.

### AUT-02 — interfaces de servicio y binding de dobles (aprobada por Kevin, 2026-08-19)

- La línea frontend puede crear exclusivamente estos archivos, con las firmas ya fijadas por Arquitectura en `docs/contratos/servicios-aplicacion.md`:
  - `app/Dominios/<Dominio>/Contratos/*.php` — las cuatro interfaces, solo declaración;
  - `app/Compartido/Errores/ErrorDeAplicacion.php` — la excepción base con `getCodigo()` y `getDetalle()`;
  - `app/Providers/DoblesServiceProvider.php` y su registro en `bootstrap/providers.php`;
  - `config/dobles.php` y las variables correspondientes en `.env.example`.
- **No** habilita ninguna otra escritura en `app/Dominios/` fuera de `Componentes/` y `Contratos/`, ni lógica de negocio, ni migraciones, ni implementaciones reales de servicio.
- Las firmas no se alteran: un cambio de firma es un cambio de contrato y vuelve a Arquitectura.
- Vence al habilitarse B01. Los sprints `B` implementan estas interfaces tal como están.

## Delegación

### Delegación — 2026-08-18/19 (CERRADA)

Kevin regresó el 2026-08-19 a las 05:39 (hora de Perú), antes del vencimiento de las 08:00. La delegación queda **cerrada por regreso del usuario**: desde ese momento las decisiones se toman en vivo y ninguna autorización se ejerce ya por delegación. Lo ejecutado bajo su vigencia: la validación y el cierre de D01, y el arranque de la cadena de frontend. Ninguna decisión de las explícitamente no delegadas fue tomada — persistencia, ADR y los tres puntos abiertos siguen sin resolver, y ningún sprint `B` se habilitó.

Términos que tuvo mientras estuvo vigente (se conservan como registro):

- Alcance autorizado: **[F00, F01, F02, F03, F05, F06, F07, D01]** — lista cerrada y literal
- Se detiene al: completar la lista, o al vencimiento, lo que ocurra primero
- Vencimiento: **2026-08-19 08:00 (hora de Perú)**
- Sesiones alcanzadas: COORDINADOR (gobernanza/registro), FRONTEND (cadena F00→F07), DEVOP (D01)
- Límite Git autorizado: hasta `develop` — la rama `main` queda prohibida sin excepción (incluye no abrir nuevos PRs hacia `main` durante la vigencia)
- Modalidad ordenada por Kevin para la línea frontend: la cadena completa F00 → F01 → F02 → F03 → F05 → F06 → F07 se ejecuta en un solo worktree (`~/hurioscan-F00`, rama `sprint/F00`), con commit propio por sprint (cuerpo `Sprint: <id>`) antes de pasar al siguiente, y un único PR hacia `develop` al final. Si un sprint no cierra sus criterios, la cadena se detiene ahí, se registra en el handoff y no continúa.
- **Waiver de rutas (aprobado por Kevin, 2026-08-19):** durante la vigencia de esta delegación, la línea frontend puede **agregar** rutas nombradas en `routes/web.php` apuntando únicamente a sus propios componentes o vistas — nunca modificar ni borrar rutas existentes. Los nombres siguen los canónicos fijados en `docs/frontend/integracion.md`. El Coordinador audita el archivo completo antes de integrar a `develop`. El permiso muere con la delegación; la regla de `AGENTS.md` (dueño backend, provisionales del Coordinador) vuelve a regir después.
- Ante un bloqueo fuera del perímetro: detener solo el sprint afectado y continuar con el resto
- Decisiones explícitamente NO delegadas:
  - aprobar el modelo de persistencia (`docs/persistencia/modelo.md`) o los ADR
  - habilitar cualquier sprint B
  - el formato de documento de identidad (¿carné de extranjería además del DNI?)
  - el límite de 15 MB por hoja
  - el motor de OCR de producción
- Aprobado por: **Kevin**
- Fecha de aprobación: 2026-08-19 (madrugada; instrucción dictada la noche del 2026-08-18)
- Estado: **CERRADA** por regreso del usuario, 2026-08-19 05:39

### Bitácora de la delegación

Decisiones tomadas mientras Kevin no estaba disponible, consolidadas a su regreso. Las tres quedaron confirmadas por él el 2026-08-19.

| # | Sprint | Qué se decidió | Alternativas descartadas | Por qué | Reversible | Dónde quedó |
|---|---|---|---|---|---|---|
| 1 | D01 | Verificar la corrida real del flujo de `main` con un PR borrador temporal hacia `main`, cerrado sin fusionar | Esperar a un PR `develop`→`main` real; confiar solo en `workflow_dispatch` | `workflow_dispatch` no funciona hasta que el archivo está en la rama por defecto, y el criterio de cierre exigía una corrida real | Sí — el PR se cerró sin fusionar y no dejó rastro en `main` | `docs/handoffs/D01.md`; PR #20 |
| 2 | D01 | Aceptar como cumplido el criterio del rojo provocado con la evidencia del paso Tests, dejando Lint y Build por inferencia estructural | Provocar además un fallo de lint y uno de build | Los tres pasos comparten shell, sin `continue-on-error` ni `if:`; el costo de dos corridas más no aportaba certeza proporcional | Sí — se puede provocar en cualquier momento | `docs/handoffs/D01.md` § Observaciones |
| 3 | F06 | Adoptar `pacientes.detalle` como nombre canónico de `/pacientes/{id}` | Dejarlo sin nombre hasta que backend lo declarara | Faltaba en la lista canónica y sigue exactamente el patrón de `sesiones.detalle` y `documentos.detalle`; ratificado por Arquitectura | Sí — es solo un nombre de ruta | `docs/frontend/integracion.md` |

## Aviso por pérdida silenciosa — APROBADO, y QA-F-04 abierto

Validado por QA sobre `develop @ 8c15081` con archivos reales inyectados en el navegador, de modo que el evento dispara el puente en JavaScript que la suite no puede ejercer.

**Unidad APROBADA.** Con el tope bajo y 25 archivos, el aviso nombra las tres cifras y dice qué hacer: «Elegiste 25 hojas y solo se procesaron 10. El equipo descartó 15 sin avisar… Vuelve a agregar las que faltan en tandas más pequeñas.» En el caso inverso —rechazos con motivo por peso y por formato— **no** aparece aviso, que es lo correcto: lo ya explicado no es pérdida. Y en el caso cruzado, que es donde esta aritmética suele romperse —25 elegidas con tope 10 y la primera de 16 MB—, los dos mecanismos conviven sin contarse dos veces: 9 capturadas más 1 rechazada con motivo son 10 resueltas, y el aviso informa 25/10/15.

Con la regresión de captura verificada sobre ese SHA, **el veredicto de F03 traslada limpio** desde `f5ca21e`.

Las dos correcciones de `LimiteDeSubidaTest` quedaron verificadas por mutación en ambas direcciones: la guarda se autodetecta si el framework cambia su redacción, y la frase que niega es la que de verdad aparece al revertir el techo — o sea que ya no es vacua.

### QA-F-04 — un aviso de PHP rompe la subida entera

**Severidad media-alta. Abierto.** Propietario: DevOps la parte accionable, Arquitectura la de fondo.

Con `max_file_uploads` bajo **y `display_errors=On`** —el valor por defecto de la máquina— superar el tope no produce «10 hojas y un aviso», produce **nada**: cero hojas, cero rechazos, ningún aviso, y solo un error de parseo en la consola del navegador. PHP trunca los archivos correctamente e imprime además su advertencia **dentro del cuerpo de la respuesta**, antes del JSON; el parseo de Livewire revienta y la subida se pierde completa. QA lo confirmó por contraste: con `display_errors=Off` el aviso funciona perfecto.

**Deja al operador peor que antes de que existiera el aviso:** antes veía menos hojas de las que eligió; ahora no ve ninguna y tampoco un error. Es la misma pérdida silenciosa que la unidad vino a eliminar, un escalón más arriba. Y encadena con la deuda del arranque ya registrada: quien use `composer dev` directo corre con `max_file_uploads=20` **y** `display_errors=On` a la vez, de modo que un lote de 25 hojas —normal en un folder clínico— produce silencio absoluto.

**El aviso no introdujo este defecto, y conviene leerlo bien:** QA-F-04 nace de cómo conviven un aviso de PHP y el parseo de Livewire, y estaba ahí desde antes de que el aviso existiera — el mismo lote ya rompía igual, solo que nadie lo había ejercido. Lo que hizo el aviso fue dar una razón para ejercer ese caso. Se deja dicho explícitamente porque dentro de unas semanas es fácil leer «defecto encontrado al validar el aviso» y concluir que el aviso lo causó. Tampoco **incumple** el criterio de cierre de esa unidad, que se cumple y está demostrado. Resolución en dos partes, con el mismo patrón que la deuda anterior:

- **Despachado a DevOps:** fijar en `scripts/php/hurioscan.ini` que ningún aviso de PHP pueda contaminar el cuerpo de una respuesta, sin cambiar una pérdida silenciosa por otra —los avisos deberían quedar registrados en algún lado, no simplemente apagados—.
- **Deuda técnica para B01:** que un aviso del motor pueda romper una respuesta JSON en cualquier punto de la aplicación es un problema de fondo, no de esta configuración. El ajuste de DevOps cierra el caso soportado, no la clase entera.

**Corregida la parte accionable** (`display_errors=Off` más `log_errors=On` en `scripts/php/hurioscan.ini`), verificada reproduciendo el defecto antes y comprobando después que la misma petición devuelve JSON limpio y parseable, con el aviso registrado en la salida de error del servidor.

Sobre la duda de si eso apaga visibilidad en desarrollo, DevOps la resolvió midiendo en vez de opinando: **Laravel ya fuerza `display_errors=Off` al arrancar**, aunque `APP_DEBUG` sea verdadero — lo que muestra los errores en desarrollo es el propio framework, no esta directiva. Lo único que cambia el ajuste es la ventana **anterior** al arranque del framework, que es justo donde vive el defecto y donde ni Laravel ni la aplicación pueden intervenir, porque esos avisos ocurren antes de que se ejecute una línea de código. Coste en visibilidad: ninguno.

También evaluó encaminar los avisos a un archivo y decidió no hacerlo: con `log_errors=On` y sin destino explícito, PHP escribe a la salida de error del proceso, que en desarrollo es la terminal donde se arrancó — misma convención de logs a `stderr` que ya declara `AGENTS.md` § Observabilidad, y sin agregar un artefacto que gestionar. Queda anotado que **cuando exista un servidor web real convendrá un destino explícito**.

### Anotado por DevOps para quien tenga la autoridad

Que `composer dev` delegue en `scripts/servir-desarrollo.sh` cerraría el agujero del arranque directo en el punto de entrada más usado. Hoy la única defensa es el encabezado que advierte, **y una advertencia depende de que alguien la lea**. `composer.json` es ruta de la línea backend, así que la decisión es suya, no de DevOps.

### Revisión de cobertura ficticia — aprobada por Kevin, pendiente de despacho

Kevin aprobó revisar las 135 pruebas buscando aserciones que no puedan fallar nunca, como unidad propia de QA. Criterio de QA que sustentó la decisión: los tres casos conocidos fallaron **de la misma manera** —la prueba comprobaba una cadena de texto en vez del hecho—, y esa firma reconocible hace la búsqueda barata: se va directo a las aserciones sobre literales y se pregunta de cada una si puede fallar alguna vez. El foco, por rendimiento esperado, va a las aserciones sobre texto de terceros (obsolescencia silenciosa), a las que niegan cadenas que quizá nunca existan (no fallan por definición) y a las pruebas cuya premisa dependa del entorno en vez de fabricarla.

**Encuentra cobertura ficticia, no defectos**, y así se declaró antes de aprobarla: puede terminar en «cuarenta aserciones revisadas, tres no servían» sin ningún fallo de producto nuevo. Se aprobó igual porque cada aserción vacua es una alarma apagada de la que alguien se fía, y porque el precio se paga entero cuando el backend se apoye en esa suite. **Condición de alcance, puesta por QA:** cada aserción declarada vacua se demuestra mutándola; declararla por lectura sería cometer el mismo error que se busca. Si al mutarla el test falla por **otra** aserción, eso ni la salva ni la condena: hay que aislarla, como se hizo con la del `may`/`must`.

**El alcance no es releer las 135 pruebas.** Es ir a las aserciones sobre literales de texto y preguntarse de cada una si puede fallar alguna vez. La corrección que hizo la línea frontend —derivar la frase de la traducción real del framework en vez de copiarla a mano— es el patrón de solución para el primero de los tres focos, y conviene tenerla como referencia al despachar.

## Cierre de la línea frontend — 2026-08-19

La cadena `F00`→`F07` está construida, integrada en `develop` y validada. **Ningún sprint pasa a `COMPLETADO`**, y no por un trámite pendiente: el roadmap declara que un sprint de frontend terminado contra su doble llega hasta `EN_VALIDACION`, y que cerrarlo exige el punto de integración con su sprint `B` —reemplazar el doble por el servicio real y verificar el flujo completo—. Esos puntos no existen porque la línea backend no ha empezado.

### Qué queda entregado

Dieciséis vistas servidas en su URL con nombres de ruta canónicos, sobre once componentes compartidos. Los siete sprints con veredicto favorable de QA, validados en navegador real sobre las páginas montadas. La costura entre líneas fijada y ejercida: cuatro interfaces de dominio con sus firmas, sus dobles activables solo por configuración, y la excepción base de errores.

**Ese último punto es el valor menos visible y el más importante para lo que viene:** los contratos que B01–B07 deben implementar no están solo escritos, están *ejercidos*. Las firmas se usaron de verdad contra dobles, y ese uso ya destapó una operación que faltaba declarar (`GET /sesiones/{id}/hojas`) y una respuesta cuya forma divergía del contrato. El backend recibe interfaces probadas, no supuestas.

### Nueve defectos, un solo patrón

Ninguno era visible desde la suite de pruebas. En orden de aparición: el desborde de `<x-tabla>` que solo se manifestaba dentro de un contenedor flex real; la ausencia del layout de página, que `Livewire::test()` nunca ejerce; el método invocado desde producción que solo existía en el doble; la forma de respuesta en la que doble y componente coincidían entre sí pero no con el contrato; el manifiesto de assets ausente en el runner; la prueba que dependía de que no hubiera rutas montadas; quince controles sin destino; el error 500 con traza ante identificadores no numéricos; y el rechazo por tamaño que nunca ocurría y se llevaba el lote entero.

El patrón común está en «Lección de método» más abajo. Los remedios que quedaron en el repositorio: `PaginaRealTest` (sirve los quince componentes como páginas reales), `AccionesConDestinoTest` (ninguna acción sin destino), `PerdidaSilenciosaTest` (el aviso por discrepancia), `LimiteDeSubidaTest` (el criterio de las capas, sostenido por el CI), y la comprobación por reflexión entre cada doble y su interfaz.

### Lo que hereda la línea backend

- **B01:** la deuda de que la aplicación verifique sus propios límites de subida al arrancar y falle de forma visible.
- **B03:** la validación de tipo y tamaño debe ser efectiva del lado del servidor. Hoy la hace el doble y el atributo `accept` del input es solo ayuda del navegador — que la interfaz se comporte bien no demuestra nada sobre seguridad. Hereda también el `wire:key` duplicado que produce el doble entre lotes, que desaparecerá al asignar identificadores reales.
- **Todos los `B`:** las firmas de `docs/contratos/servicios-aplicacion.md` se implementan, no se redefinen. Un cambio de firma es un cambio de contrato y vuelve a Arquitectura.
- **Al habilitarse B01 vencen AUT-01, AUT-02 y AUT-03**, y `routes/web.php`, las interfaces y `config/livewire.php` vuelven a los dueños que declara `AGENTS.md`.

### Lo único que falta del lado frontend

Nada de construcción. Queda **QA-F-04 abierto**, cuya parte accionable está despachada a DevOps, y la revisión de cobertura ficticia aprobada y pendiente de despacho a QA. Ver arriba.

## Lección de método — cobertura ficticia, 2026-08-19

En dos días aparecieron **tres casos del mismo patrón**, y la repetición es lo que lo convierte en lección y no en anécdota:

1. `<x-tabla>` desbordaba en 360 px, pero solo al reutilizarse dentro de un contenedor flex real; el catálogo de componentes no la ejercía así y por eso pasaba.
2. La prueba de acciones con destino comprobaba la subcadena `disabled`, que aparece dentro de las clases de estilo (`disabled:opacity-50`), de modo que **cualquier** botón la satisfacía — incluido uno inerte.
3. Una guarda contra el mensaje del framework buscaba `may not be greater than`, redacción que la versión actual de Laravel ya no usa: la cadena aparece **cero veces** en el paquete, así que esa aserción no podía fallar nunca.

En los tres casos el código pasaba, la suite estaba verde y la cobertura era ficticia. **Lo único que los destapó fue romper algo a propósito para ver si alguien se quejaba.** Ninguno se habría encontrado leyendo el código ni mirando el resultado de la suite.

De ahí salen dos prácticas que este proyecto adopta:

- **Verificar que una prueba pasa no dice nada; lo que dice algo es verificar que puede fallar.** Antes de dar por buena una prueba que cubre un defecto recién corregido, se muta el defecto de vuelta —en una copia desechable— y se confirma que falla **esa aserción concreta**, no la suite por otra vía. Se aplicó a `AccionesConDestinoTest`, a `LimiteDeSubidaTest` y a la corrección del techo de subida.
- **Una cadena copiada a mano de un tercero se vuelve obsoleta en silencio.** La corrección de la guarda no fue reemplazar la frase por la vigente, sino verificarla contra la traducción real del framework, con un mensaje que indica qué actualizar si cambia. Reemplazar la frase habría dejado el mismo defecto latente para la próxima versión.

Queda anotada la sugerencia de revisar la suite completa con esta lente, planteada por la línea frontend: si tres aparecieron sin buscarlas, es razonable que haya más. Pendiente de que QA valore si el retorno justifica el esfuerzo, y de decisión de Kevin como unidad de trabajo propia.

## Veredicto QA de la cadena `F` — 2026-08-19

Sobre `hurioscan@9576f68`. Detalle por sprint en cada `docs/handoffs/F0*.md`.

| Sprint | Veredicto |
|---|---|
| F00 | APROBADO con observación |
| F01, F02, F05, F06, F07 | APROBADO |
| **F03** | **APROBADO** el 2026-08-19 tras corregir QA-F-03 (revalidado sobre `f5ca21e`) |

**Ningún APROBADO cierra su sprint.** Son veredictos sobre interfaz contra dobles; el paso a `COMPLETADO` exige el punto de integración con el sprint `B` correspondiente, que no existe.

### QA-F-03 — cerrado el 2026-08-19

Corregido en sus tres capas y revalidado por QA con archivos reales inyectados en el navegador, atravesando `php.ini` → Livewire → dominio. El caso que antes perdía el lote entero ahora conserva las hojas válidas y muestra la rechazada con el mensaje del producto en español; un lote de 25 hojas llega completo. Detalle en `docs/handoffs/F03.md`.

**Deuda operativa declarada, con dueño y sprint.** QA midió que `scripts/php/hurioscan.ini` solo se aplica a través de `scripts/servir-desarrollo.sh`: arrancar con `composer dev` o `php artisan serve` directos devuelve los valores por defecto (2 MB, 20 archivos) **sin ninguna señal**, que es la misma familia de pérdida silenciosa del defecto original. Resolución en dos partes:

- **Ya despachado a DevOps:** corregir la línea del encabezado del script que dice «equivale a `composer dev`», porque induce a creer que son intercambiables cuando la diferencia es todo el arreglo.
- **Deuda técnica para B01:** que la aplicación **verifique sus propios límites al arrancar** y falle de forma visible si están por debajo del límite del producto. Toca `app/Providers`, fuera de las autorizaciones vigentes; B01 tendrá autoridad natural sobre eso. No se acepta como deuda indefinida: queda con dueño y sprint asignados.

### QA-F-03 — el defecto que rechazó F03 (histórico)

El rechazo de una hoja por tamaño nunca ocurre, y en su lugar se pierde el lote entero sin mostrar ningún motivo. Detalle completo en `docs/handoffs/F03.md`. Lo esencial: **hay tres límites descoordinados y ninguna capa está configurada para respetar el que declara el producto** — producto 15 MB, Livewire 12 MB por su valor por defecto, y `upload_max_filesize` de PHP 2 MB en la máquina de validación. El defecto no depende de qué número se elija; depende de que nadie configuró la plataforma.

Corregirlo abarca configuración de aplicación (`config/livewire.php`, que no existe) y de entorno (PHP, y el servidor web cuando exista), así que **cruza la frontera de rutas escribibles de `AGENTS.md` y necesita una decisión de gobernanza antes de despacharse.**

### Decisión de Arquitectura sobre la observación de F00

QA midió las dieciséis páginas en los cuatro anchos y solo `/componentes` desborda, unos 12 px en 360. QA no la absolvió por su cuenta y derivó la llamada, correctamente, porque RNF-004 dice literalmente «aplica a todas las vistas».

**Resuelto como observación, no como no conformidad.** El catálogo de componentes es una vitrina de desarrollo: su ruta solo se registra bajo entorno local, nunca se sirve, y F00-UT-05 no le pide comportamiento responsive. «Todas las vistas» de RNF-004 se entiende referido a las vistas del producto. Queda anotado en el handoff de F00: si alguna vez el catálogo se expusiera fuera de local, dejaría de ser observación.

### Riesgo que hereda B03

La validación de tipo y tamaño hoy la hace el doble, y el atributo `accept` del input es solo ayuda del navegador, no un control. Cuando B03 implemente el servicio real, el rechazo por formato y por tamaño tiene que ser efectivo del lado del servidor: **que hoy se vea correcto no lo demuestra.**

### Observación sobre la prueba de acciones con destino

QA mutó `AccionesConDestinoTest` en una copia desechable y confirmó que sí atrapa el defecto original. Anotó un matiz que conviene conocer: el barrido descarta los controles que llevan `wire:loading`, así que un botón inerte que además lo usara quedaría fuera de vigilancia sin avisar. Hoy no ocurre, pero `wire:loading.attr="disabled"` es un idioma habitual de Livewire. No es defecto del producto; es una arista de la red que conviene cerrar cuando se toque ese test.

## Validación QA de la cadena `F` — parcial, 2026-08-19

QA validó sobre `develop @ d6d2f40` en worktree propio con dobles activados, servidor real y navegador. **La validación quedó incompleta y sin veredicto** por el corte de la sesión; lo que sigue son los resultados concluyentes, no un veredicto de sprint.

### Defectos confirmados

**QA-F-01 — dos componentes inalcanzables desde la aplicación servida.** Severidad alta. Afecta F02-UT-03 y F03-UT-02.
`FormularioPaciente` (alta de paciente) y `AperturaSesion` no tienen ruta montada y ningún archivo del árbol los referencia. Los botones que deberían conducir a ellos existen y se ven habilitados, pero no llevan ninguna directiva: en `resources/views/dominios/pacientes/buscador.blade.php` salen como `<x-boton>` sin `wire:click` ni `href`, así que un clic real no hace nada. Consecuencia: desde la interfaz servida no se puede registrar un paciente ni abrir una sesión de digitalización.

**La causa de fondo es de Arquitectura, no de la línea frontend ni del montaje.** La lista de nombres canónicos de `docs/frontend/integracion.md` omitía `pacientes.alta` y `sesiones.apertura`, pese a que las unidades de trabajo de F02 y F03 las fijan como su interfaz. El montaje declaró exactamente los catorce nombres de esa lista, o sea hizo bien su trabajo sobre una entrada incompleta. La lista ya está corregida en ese documento, con la lección: **al derivar nombres canónicos hay que recorrer las unidades de trabajo de cada RFC, no solo los flujos de `experiencia.md`.**

**Por qué ninguna prueba lo detectó**, y es el séptimo caso del mismo patrón: `tests/Feature/Frontend/PaginaRealTest.php` registra su propia ruta para cada componente antes de servirlo. Comprueba que cada componente *puede* renderizarse como página, nunca que la aplicación real *llegue* a él. La prueba se apoya en una condición que ella misma fabrica.

**QA-F-02 — deny-by-default inconsistente sin sesión.** Severidad media. Afecta F02-UT-02, contra RNF-013.
El layout resuelve el rol como `null` sin sesión y el menú no muestra ninguna opción, que es correcto; pero `BuscadorPacientes` declara `public string $rol = 'operador'`, un default privilegiado. En la página servida sin sesión conviven un menú vacío y el botón «Registrar paciente nuevo», reservado a operador y administrador. Hoy no concede acceso real porque no hay backend, así que no es una fuga de permisos, pero el estado por defecto de la aplicación servida es el privilegiado en vez del restringido.

### Verificado en verde

No hace falta rehacerlo mientras no cambie su entrada: proveniencia y suite (103/103, `pint` passed) reproducidas en worktree propio; las catorce rutas responden 200; **RNF-012 en sus dos puntos** sobre HTML renderizado, incluido el caso negativo de buscar un payload como término, sin inyección; **conflicto de versión** reproducido con sus dos salidas explícitas y sin reenvío silencioso; sin fuga de hash de contraseña en `/usuarios` con la página listando usuarios reales; sin jerga del proveedor de identidad en los cuatro desenlaces del alta por DNI; y `/pacientes` a 360 px sin desborde, con la tabla scrolleando dentro de su contenedor.

### Pendiente al retomar

Barrido responsive completo de las catorce páginas en los cuatro anchos —midiendo página por página, porque medirlas juntas en un iframe agotó el tiempo del navegador—; criterios de cierre detallados de F05, F06 y F07; la superficie de subida de archivos de F03-UT-03, que es la de mayor carga de riesgo y quedó sin ejercer; accesibilidad sobre páginas servidas; y el cierre de `seguridad-validacion`, en curso y **sin hallazgos bloqueantes hasta el corte**.

### Limitación de entorno declarada

`AGENTS.md` declara PostgreSQL 18.3 para validación, pero el servidor local exige una credencial que QA no tiene, así que sesión y caché corrieron sobre SQLite. QA verificó antes que ningún componente de dominio toca la base —sin Eloquent ni `DB::` en `app/Dominios` ni `app/Compartido`, y solo las tres migraciones base de Laravel—, de modo que el motor no puede enmascarar nada de lo validado. **Para los sprints `B` sí hará falta PostgreSQL real: conviene dejar disponible la credencial local antes de arrancar el backend.**

## Punto de retomada — pausa del 2026-08-19

Sesión pausada por ausencia de Kevin. **Todo el trabajo está persistido en `origin`**: ningún worktree tenía cambios sin commitear y ninguna rama local commits sin empujar, verificado antes de cortar. Nada quedó vivo solo en la memoria de una sesión.

- **Estado del árbol:** `develop @ d6d2f40`, con `pint` passed, 103/103 tests y las catorce rutas montadas.
- **Worktrees conservados:** `~/hurioscan-F00` en `sprint/F00 @ adabd5b` y `~/hurioscan-D01` en `sprint/D01 @ 9e9a839`, ambos limpios y ya integrados. El de D01 sigue pendiente de una decisión de Kevin sobre si se elimina.
- **QA alcanzó a reportar antes de cortar:** validación parcial, sin veredicto, con dos defectos confirmados y buena parte de los criterios en verde. Todo registrado en la sección anterior, así que no hay que redescubrirlo — solo completar lo que quedó pendiente allí.

### Qué sigue, en orden

1. **Corregir QA-F-01**, que es lo que bloquea el cierre de F02 y F03: la lista canónica ya está corregida con `pacientes.alta` y `sesiones.apertura`, así que falta montar esas dos rutas y cablear los botones que hoy no conducen a ninguna parte. Corregir también QA-F-02 (el default privilegiado de rol). Ambos son trabajo de la línea frontend sobre una entrada de Arquitectura ya resuelta.
2. **Retomar la validación de QA** sobre el SHA nuevo que resulte de esa corrección, completando lo que quedó pendiente y revalidando lo afectado. Lo verde ya registrado se reutiliza mientras su entrada no cambie.
3. **Con un `APROBADO`**, el Coordinador registra el veredicto en los handoffs. Los sprints `F` **no** pasan a `COMPLETADO` con eso: el roadmap exige el punto de integración con su par `B`, que todavía no existe.
4. **Decisión pendiente de Kevin, que es la que destraba todo lo demás:** aprobar `docs/persistencia/modelo.md` y los cinco ADR. Sin eso B01 no llega a `LISTO` y la línea backend sigue en cero — no hay modelos, servicios ni migraciones propias, solo las tres que Laravel trae de fábrica.
5. Al habilitarse B01 vencen **AUT-01** y **AUT-02**: `routes/web.php` y las interfaces vuelven a su dueño natural según `AGENTS.md`.

Cualquier sesión que retome reconstruye desde aquí y desde Git, nunca desde la conversación anterior.

## Chats de rol activos

Índice de qué sesión trabaja en qué. Lo mantiene el Coordinador: se agrega una fila al despachar un chat y se actualiza al recibir su handoff. No sustituye al handoff versionado de cada sprint — es el mapa de quién está haciendo qué.

| Rol | Línea | Sesión (chat principal) | Sprint activo | Depende de | Estado del chat | Estado del sprint | Último handoff |
|---|---|---|---|---|---|---|---|
| coordinacion | — | COORDINADOR (este chat) | — | — | ABIERTO | — | — |
| implementation | frontend | FRONTEND | cadena F00→F07 | — | ABIERTO | los siete EN_VALIDACION, integrados en `develop` | `docs/handoffs/F00.md` (ancla) y uno por sprint |
| implementation | backend | (sin despachar) | B01 | aprobación de persistencia/ADR | — | PLANIFICADO | — |
| devops | — | DEVOP | D01 | ninguna | ABIERTO | COMPLETADO | `docs/handoffs/D01.md` |
| qa | — | QA | cadena F00→F07 (por despachar) | ninguna | ABIERTO | — | `docs/handoffs/D01.md` (D01 APROBADO) |

> Worktrees activos: `../hurioscan-F00` en `sprint/F00` y `../hurioscan-D01` en `sprint/D01`, ambos desde `develop @ bb7ae5b`. Ver "Aislamiento del trabajo paralelo" en `AGENTS.md`. Los cuatro chats se comunican por mensajería directa entre sesiones (SendMessage); el Coordinador es COORDINADOR.

**Regla de despacho con dos líneas en paralelo:** antes de abrir un chat, el Coordinador verifica que su sprint no comparta rutas escribibles con el sprint activo de la otra línea, según la separación declarada en `AGENTS.md`. Si aparece un archivo compartido que la separación no previó, esa línea base resultó incorrecta: se pausa y se corrige `AGENTS.md` antes de continuar, en vez de dejar que dos agentes se pisen.

## Referencias
- Roadmap: `docs/roadmap.md`
- Handoff activo: `docs/handoffs/F00.md` (en rama `sprint/F00`)
- Decisiones/contratos: `docs/decisiones/`, `docs/contratos/`
