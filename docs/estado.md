---
project: HuriosCan
source_status: CANONICA
baseline: línea base completa y aprobada — RF, RNF, contratos, modelo, errores, permisos y los cinco ADR
active_phase: B01
active_status: EN_VALIDACION
last_completed_phase: D01
bootstrap_status: COMPLETO
planning_horizon_status: PARCIAL — frontend en EN_VALIDACION; B01 habilitado, B02–B07 con RFC en propuesto
current_rfc_batch: [D01, F00, B01, F01, B02, F02, B03, F03, B04, F05, B05, F06, B06, F07, B07]
planning_scope: [RF-001, RF-002, RF-003, RF-004, RF-005, RF-006, RF-007, RF-008, RF-009, RF-010, RF-011, RF-012, RF-013, RF-014, RF-015]
updated_at: 2026-08-21
repositories:
  - name: hurioscan
    path: ~/hurioscan
    branch: develop
    current_sha: 914f633e43e79d56b6f73ec09d96533aee616642
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
    planning_status: LISTO
    execution_status: EN_VALIDACION
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

## Cómo se lee este documento

Mezcla dos clases de sección y conviene distinguirlas antes de creerle a ninguna:

- **Estado vigente** — describe cómo están las cosas *ahora*. Se corrige en cuanto
  la realidad lo contradice. Son las secciones sin fecha en el título, más el
  frontmatter.
- **Instantánea fechada** — «Punto de retomada — pausa del 2026-08-19»,
  «Validación QA de la cadena `F` — parcial, 2026-08-19», las bitácoras de
  delegación y las auditorías con fecha. Registran lo que se sabía *ese día*.
  **No se corrigen aunque hoy sean falsas:** corregirlas falsificaría el registro
  de lo que se sabía entonces. Se leen como historia, nunca como instrucción.

**Consecuencia práctica:** si una instantánea y una sección vigente se
contradicen, manda la vigente — y por encima de las dos, manda Git. Una
instantánea que sigue mandando trabajo ya hecho es la trampa que este proyecto ya
pisó una vez (ver «Punto de retomada — 2026-08-21»).

## Si estás retomando esto y no viste nada antes, lee esto primero

**Qué es HuriosCan.** Un sistema para digitalizar el archivo clínico en papel de un establecimiento de salud: se escanean las hojas de cada folder, un OCR extrae su texto, una persona lo revisa, y después el documento se puede buscar por lo que dice adentro. Proyecto de curso universitario, cinco integrantes, doce semanas, dos personas programan. Laravel 13 + Livewire + PostgreSQL, un solo repositorio.

**En qué punto está, en una frase:** toda la interfaz está construida y validada contra datos ficticios, el backend acaba de empezar y su primer sprint está bloqueado por un defecto de seguridad.

**Qué hay hecho de verdad**
- **D01 — COMPLETADO.** Integración continua: cada PR hacia `develop` corre lint, pruebas y build; cada PR hacia `main` añade la suite contra PostgreSQL real. Funciona y ya detuvo defectos reales.
- **F00 a F07 — EN_VALIDACION, aprobados por QA.** Las dieciséis pantallas del sistema, navegables en sus URLs, verificadas en cuatro anchos de pantalla y con revisión de accesibilidad. **No están `COMPLETADO` y eso es correcto:** funcionan contra *dobles de desarrollo*, piezas que devuelven datos fijos en lugar de consultar una base. Cada sprint `F` se cierra recién cuando su sprint `B` lo reemplaza por el servicio real.
- **La línea base documental completa y aprobada:** 15 requisitos funcionales, los no funcionales, cuatro contratos de dominio, el modelo de persistencia con nueve migraciones, la taxonomía de errores, la matriz de actores y permisos, y cinco ADR.

**Qué NO hay.** No hay sistema funcionando. Sin base de datos poblada, sin autenticación real en uso, sin OCR, sin búsqueda. Si abres la aplicación con los dobles encendidos verás algo que parece el producto, pero nada se guarda.

**Dónde está cada cosa**
- `docs/roadmap.md` — los quince sprints, sus dependencias y qué RF cubre cada uno.
- `docs/rfcs/<id>.md` — qué construye cada sprint y sus criterios de cierre. **Un sprint no se ejecuta si su RFC no está `aprobado`.**
- `docs/handoffs/<id>.md` — qué hizo cada sprint ya ejecutado. Los de la cadena `F` viven en `develop`; el de **B01 vive en la rama `sprint/B01`**, sin fusionar.
- `AGENTS.md` — quién puede escribir qué, la política de ramas y cómo se despacha el trabajo. **Léelo antes de tocar nada.**
- Este archivo — el estado, las decisiones y los bloqueos.

**Lo primero que necesitas saber para trabajar:** `main` y `develop` están protegidas en GitHub. Nada entra sin PR y sin CI en verde, ni siquiera siendo administrador. Un sprint se ejecuta en su propio worktree, en una rama `sprint/<id>`, y entra por PR hacia `develop`.

**Qué sigue, en orden**
1. ~~**Desbloquear B01** corrigiendo QA-B01-01~~ → **corregido el 2026-08-21**; el sprint está en `EN_VALIDACION` y QA lo revalida. Ver la sección de B01 más abajo.
2. **Aprobar el RFC de B02** —lo aprueba Kevin, sobre el documento completo— para habilitar el siguiente sprint. Todas sus fuentes ya están aprobadas; solo falta su firma.
3. Los pares `B`/`F` se integran de a uno: B01 con F01, B02 con F02, y así. Recién ahí cada sprint `F` pasa a `COMPLETADO`.

**Qué espera decisión de Kevin, y nadie más puede resolver:** el RFC de B02, el del punto de integración B01 + F01, y el RNF nuevo del límite de intentos de acceso — los tres se le presentan juntos para aprobar. **Ya no esperan nada:** los dos huecos de `AGENTS.md` (cerrados el 2026-08-20) y el despliegue de Vercel (Kevin aprobó eliminarlo el 2026-08-21; el borrado del directorio queda de su mano).



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
- **Línea frontend:** la cadena F00 → F07 **ya se ejecutó completa** (2026-08-18/19), está integrada en `develop` y los siete sprints tienen veredicto favorable de QA. Todos en `EN_VALIDACION`; ninguno pasa a `COMPLETADO` hasta su punto de integración con el sprint `B` correspondiente. No queda trabajo de construcción de frontend.
- **D01:** `COMPLETADO` e integrado. Todo PR hacia `develop` ejecuta ahora lint, tests y build automáticamente.
- **Backend:** ~~bloqueado hasta que Kevin apruebe `docs/persistencia/modelo.md` y los ADR~~ → **ese motivo caducó el 2026-08-19**, cuando Kevin los aprobó. B01 se habilitó, se ejecutó y quedó bloqueado por **otro** motivo, vigente y distinto: `QA-B01-01`. Ver la sección propia más abajo. El motivo escrito aquí sobrevivió un día entero a su propia caducidad, que es exactamente el modo de falla que la sección «Cómo se lee este documento» existe para prevenir.

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

## B01 — QA-B01-01 corregido, en revalidación desde el 2026-08-21

> **El reposo terminó.** Kevin retomó el proyecto el 2026-08-21 y ordenó arrancar
> la línea backend. La decisión de «no se reintenta» era el criterio de un punto de
> reposo, no una prohibición permanente: se aplicaba mientras el proyecto estuviera
> detenido. El sprint volvió a despacharse.

**Estado actual: `EN_VALIDACION`, con QA `APROBADO` e integrado en `develop`.**
La línea backend corrigió el defecto, QA lo revalidó y emitió **APROBADO** sobre
`8b1763c` el 2026-08-21, y el PR [#50](https://github.com/Espiritu16/hurioscan/pull/50)
se fusionó a `develop` con el gate cumplido y el CI en verde.

**Sigue sin pasar a `COMPLETADO`, y es correcto.** El roadmap declara que *«un par
`B`/`F` no está cerrado hasta que se integran de verdad»*, y el punto de integración
**B01 + F01** —reemplazar el doble de usuarios por el servicio real, comprobar que se
accede con los tres roles y que cada uno ve su menú, y confirmar que el build no cae
de vuelta al doble— **todavía no se ha hecho**. Es trabajo real, no un trámite.

Detalle completo del veredicto, las tres mutaciones y lo que QA reutilizó:
`docs/handoffs/B01.md` § Validación QA.

| Qué | Valor |
|---|---|
| Rama | `sprint/B01`, publicada en `origin` |
| `final_sha` **vigente** | `b0049709ee588ba989be6368249feca0ea07dcc5` (código); head con el handoff: `8b1763cd28643b9df2c073985936227a4fa713fb` |
| `final_sha` **rechazado** (histórico) | `17f3162a3bee3f7a1d1daa906006de06b03cf41d` — el que QA rechazó. Ya **no** es el SHA a validar |
| PR | [#50](https://github.com/Espiritu16/hurioscan/pull/50) — **fusionado a `develop` el 2026-08-21**. Su cuerpo se reescribió con el veredicto antes de fusionar: el aviso «BLOQUEADO — NO FUSIONAR» describía un defecto ya corregido, y un cartel así engaña tanto como un verde sobre trabajo rechazado |
| Worktrees | se retiran tras la integración; el contenido vive en `develop` y en la rama `sprint/B01` |

### La corrección, verificada por Coordinación contra el árbol real

Dos archivos, ambos dentro del área autorizada de la línea backend: `bootstrap/app.php`
y `tests/Feature/Backend/AccesoTest.php`.

- `ErrorDeAplicacion` deja de pasar por el reporte por defecto de Laravel —el camino
  que escribe el stack trace, y con él los argumentos de cada frame—. En su lugar se
  registra una línea estructurada con `codigo`, `estado` y `origen`. **No se cambió una
  pérdida silenciosa por otra:** se pierde solo la cadena de llamadas, que es justo lo
  que transportaba el secreto. El nivel se deriva del status ya aprobado en
  `manejo-errores.md` (4xx → `info`, 5xx → `error`), sin inventar una segunda tabla.
- La fixture bajó de **18 a 8 caracteres** y el test pasa a proveedor de datos con 8,
  15 y 18, sobre los dos caminos de error. Para el caso de 18 se afirma además sobre
  los **primeros 15 caracteres**, porque el trace filtra un prefijo y sin eso ese juego
  de datos volvería a ser incapaz de fallar — el mismo error que ocultó el defecto.

**Mutación reproducida por Coordinación**, no solo reportada: revertida la corrección,
los tres juegos de datos fallan y el log contiene literalmente
`autenticar('operador@hurios..', 'clave-08-mal', false)`. Restaurada, vuelven a verde y
la contraseña no aparece en `storage/logs/`. La prueba ya puede fallar por lo que dice
vigilar.

### Riesgo residual — escalado, no resuelto, y no es de la línea backend

La causa de fondo no es `ErrorDeAplicacion`: es que **los traces de PHP llevan los
argumentos** y `autenticar()` recibe la contraseña como cadena suelta. Una excepción
*inesperada* dentro de esa llamada —un `QueryException` durante el acceso— seguiría
escribiéndola. Cerrarlo del todo exige una de dos cosas, y ninguna es de esa línea:

- `zend.exception_ignore_args=1` en `scripts/php/hurioscan.ini` → **DevOps**.
  **HECHO el 2026-08-21** — ver «QA-B01-02 — cerrado» más abajo;
- cambiar la firma de `autenticar` en `docs/contratos/servicios-aplicacion.md` →
  **Arquitectura** (un cambio de firma es un cambio de contrato). **Sigue abierta**, y
  no urge: la directiva impide que los traces escriban la contraseña, pero no elimina
  la causa.

Quedó declarado en vez de decidido por cuenta propia, y QA lo juzgó explícitamente al
revalidar: no bloqueaba el veredicto, pero tampoco lo aceptó como deuda.

### El diagnóstico original, que conserva su valor


- Rama `sprint/B01`, `final_sha` **`62702cdf981524849ce009b692e5d4f2211f7b60`**, publicada en `origin`. PR [#50](https://github.com/Espiritu16/hurioscan/pull/50) hacia `develop` **abierto y sin fusionar**, con el check `verificacion` en verde. No se integra: `AGENTS.md` exige QA APROBADO antes de `develop`.
- El handoff del sprint vive en `docs/handoffs/B01.md` **dentro de la rama `sprint/B01`**, no en `develop`. Se lee con `git show sprint/B01:docs/handoffs/B01.md`.
- **Su `worktree_path` quedó retirado el 2026-08-21.** El handoff de esa rama todavía apunta a `~/hurioscan-B01`, que ya no existe: no pudo corregirse allí sin commitear en una rama bloqueada, así que se declara aquí. Nada se perdió — la rama está publicada en `origin`. Quien retome el sprint recrea el árbol con `git worktree add ../hurioscan-B01 sprint/B01` y **corrige ese campo en su primer commit**.

### QA-B01-01 — la contraseña se escribe en claro en el log en cada intento fallido

Severidad **alta**. Incumple el criterio de cierre de UT-02 («la contraseña no aparece en ningún log»), **RNF-014**, y `docs/contratos/usuarios.md`, que exige que la contraseña nunca aparezca en logs, mensajes de error ni auditoría.

**Causa raíz.** `ErrorDeAplicacion` no está excluido del reporte de excepciones —no hay `dontReport` ni `report()` en `bootstrap/app.php`, verificado por Coordinación—, así que Laravel registra un `NO_AUTENTICADO` rutinario a nivel `ERROR` con su stack trace completo. El trace incluye los argumentos de `autenticar($email, $password, $recordar)`. **PHP trunca los argumentos de tipo cadena a 15 caracteres y el validador exige un mínimo de 8: toda contraseña de entre 8 y 15 caracteres queda escrita entera y en claro.** La del seeder (`hurioscan`, 9 caracteres) cae dentro de ese rango. Ocurre en los dos caminos de error, el 401 y el 422.

**Por qué la guarda no lo atrapó — cuarto caso de cobertura ficticia, y el más grave.** `AccesoTest::test_la_contrasena_no_aparece_en_ningun_log` está bien construida, con marca de canario incluida, pero su fixture `CLAVE = 'clave-de-prueba-77'` mide **18 caracteres**: el trace la trunca a `'clave-de-prueba..'` y la comparación contra la cadena completa nunca falla. **La prueba pasaba por una propiedad accidental del dato de prueba, no porque la propiedad se cumpliera.** Verificado por Coordinación: la fixture es de 18 caracteres. Con cualquier contraseña de 15 o menos, falla.

Los tres casos anteriores están en «Lección de método» más abajo; este es el primero que afecta a una guarda de seguridad, y confirma que el patrón no se agotó con la revisión de cobertura ficticia.

### Para desbloquear cuando se retome

Corregir en la línea backend, entregar un `final_sha` nuevo y revalidar. QA declaró qué repetirá: reproducir el caso con contraseñas de 8, 15 y 18 caracteres, la mutación de canario, y la regresión completa de `AccesoTest`. **La fixture de esa prueba debe dejar de ser una contraseña larga**, porque su longitud era lo que ocultaba el defecto.

### Lo que QA verificó en verde y no hace falta rehacer

Mientras la entrada no cambie: UT-01 y RNF-005 con mutación confirmada en los dos motores (quitar el trait hace fallar la prueba en SQLite y en PostgreSQL); el schema de `usuarios` campo por campo contra el modelo aprobado; UT-03 y RNF-013 con **tres** mutaciones reproducidas —quitar una guarda hace fallar 3 pruebas, abrir la matriz hace fallar 11, anular la comprobación de usuario activo hace fallar 14—; y UT-04 con recorrido real sobre 15 rutas y 5 identidades, coincidiendo fila por fila con la matriz de permisos. Suite: 222/222 en SQLite y 215/215 en PostgreSQL 18.3, ambas reproducidas por QA y la primera también por Coordinación.

QA confirmó además que las dos decisiones de perímetro del implementador **no debilitaron nada**: eliminar el modelo huérfano no dejó residuos, y la prueba de frontend que modificó sigue atrapando lo que atrapaba, verificado inyectándole un control sin destino.

### Observaciones que quedan abiertas y no son de B01

1. **`MatrizDePermisos` perdió una condición de alcance.** `actores-permisos.md` declara para `PATCH /usuarios/{id}` la condición «no puede quitarse a sí mismo el rol administrador»; la transcripción la registra sin condición. Es la única de las 15 filas con condición que se perdió. No bloquea B01 —esa ruta no existe todavía— pero **B07 la necesita resuelta, y la decisión es de Arquitectura**: puede argumentarse que es regla de negocio con código propio y no alcance de autorización.
2. **`GET /sesiones/{id}/hojas` sigue sin fila en la matriz**, así que deny-by-default lo rechazaría. Afecta a **B05**. Resuelve Arquitectura.
3. **`POST /acceder` no tiene límite de intentos — riesgo conocido, alcance de producto.** QA verificó 12 intentos fallidos consecutivos sin ningún rechazo por frecuencia. **No es incumplimiento**: ninguna fuente aprobada lo exige — ni los RF, ni los RNF de seguridad (RNF-010 a RNF-014), ni el contrato de `POST /acceder`. Se registra como riesgo conocido por dos razones concretas: **agrava QA-B01-01**, porque cada intento fallido escribe una contraseña en el log, de modo que un atacante puede llenar el log con credenciales ajenas; y **B01 es el sprint que fija la superficie de autenticación**, así que si se decide añadirlo después será modificar algo ya construido en vez de diseñarlo. **No se resuelve por iniciativa del Coordinador: exige un RF o un RNF nuevo, y eso es alcance de producto que decide Kevin.** Si se aprueba, el sprint que lo implemente será el que corresponda a ese requisito, no B01.
4. Una justificación en la migración afirma que «la validación de aplicación rechaza igual un rol fuera del conjunto», y esa validación **no existe en este SHA** — es de B07. La decisión sigue siendo correcta; la justificación describe algo que aún no está.



## QA-B01-03 — la contraseña viaja al cliente en el snapshot de Livewire

**No conformidad abierta, severidad media. Detectada y reproducida por Coordinación el
2026-08-21**, al cerrar un hueco del criterio de parámetros sensibles que la línea
backend había señalado. **No la encontró QA** — apareció mirando por dónde más viaja el
mismo dato, que es justamente la pregunta que el criterio no se había hecho.

### El defecto

`FormularioAcceso::$password` es una **propiedad pública** de un componente Livewire, y
Livewire serializa las propiedades públicas en el snapshot que viaja al cliente y vuelve
en cada petición. Medido, no leído:

| Dónde | ¿Aparece la contraseña? |
|---|---|
| HTML renderizado | no |
| **Snapshot de Livewire** | **sí — viaja al cliente** |

Y hay un segundo filo: el componente hace `$this->password = ''` **solo en el camino de
error**. En el acceso **exitoso** (`FormularioAcceso::acceder()`, tras autenticar) la
propiedad conserva la contraseña hasta la redirección.

### Por qué es de la misma familia que QA-B01-01 y QA-B01-02, y por qué no la vieron

Los tres son «la contraseña sale por un canal que nadie estaba mirando». Los dos
primeros eran stack traces; éste es el transporte del componente. **Las correcciones
anteriores no lo tocan ni podían tocarlo:** `#[\SensitiveParameter]` protege parámetros
y esto es una propiedad; `zend.exception_ignore_args` protege trazas y esto no es una
traza.

Tampoco lo habrían visto las pruebas existentes: `PantallaAccesoTest` verifica que tras
un error la propiedad queda vacía —y eso pasa— pero nunca preguntó qué contiene el
snapshot mientras la persona escribe.

### Severidad, sin inflarla ni suavizarla

**Media.** El snapshot va al navegador de quien escribió la contraseña, así que no es
una fuga hacia un tercero por sí sola. Lo que la hace real es la superficie que abre:
queda en el DOM y en el historial de red del navegador, alcanzable por cualquier
extensión y por cualquier XSS en la aplicación. Roza **RNF-014** —«ningún log, mensaje
de error o respuesta expone credenciales»— porque el snapshot forma parte de la
respuesta.

### Quién la resuelve, y por qué no la corrigió Coordinación al encontrarla

`app/Dominios/Usuarios/Componentes/` es ruta de la **línea frontend**, y el arreglo no
es de una línea: limpiar la propiedad en el camino exitoso corrige el segundo filo pero
**no el primero**, porque la contraseña ya viajó al cliente cuando la persona la
escribió. Cerrarlo de verdad exige decidir cómo se compone la pantalla, y eso es
**Arquitectura junto con la línea frontend**, no una corrección al vuelo del Coordinador.

**No bloquea nada hoy** y no reabre B01, que QA aprobó sobre lo que se le pidió validar.
Se registra con dueño en lugar de aceptarse como deuda, igual que se hizo con
`QA-B01-02`.

## QA-B01-02 — cerrado el 2026-08-21 en la capa de entorno

`zend.exception_ignore_args=1` en `scripts/php/hurioscan.ini`, con su comentario.
Ningún código de aplicación cambió. Integrado en `develop` por el PR
[#61](https://github.com/Espiritu16/hurioscan/pull/61).

### Por qué la directiva amplia y no la estrecha — la parte que vale conservar

DevOps evaluó `zend.exception_string_param_max_len=0`, que a primera vista parece mejor
porque solo vacía los argumentos de tipo cadena en vez de todos. **Medida, deja la fuga
viva.** Reproducido de forma independiente por Coordinación:

| Configuración | `getTrace()` crudo | `getTraceAsString()` |
|---|---|---|
| Estado anterior | **FUGA** | **FUGA** |
| `zend.exception_string_param_max_len=0` | **FUGA** | limpio |
| `zend.exception_ignore_args=1` | limpio | limpio |

La estrecha solo protege la representación **en texto**. El array crudo sigue trayendo
la contraseña completa, y ése es el que consumen la página de error de depuración, un
formateador JSON o un rastreador de errores. **Habría sido una corrección aparente** —
el mismo patrón de cobertura ficticia que el proyecto ya encontró cuatro veces, esta vez
en una directiva de configuración en lugar de en una prueba. Y con la misma defensa:
provocarlo en vez de leerlo.

### El coste, sin suavizarlo

La directiva es **global**: ningún trace de la aplicación conserva ya los *valores* de
sus argumentos, solo el nombre de la función y el conteo de frames. Es pérdida real de
capacidad de diagnóstico para cualquier depuración futura. Se aceptó porque es el único
mecanismo verificado que protege el array crudo, y porque es el valor que la propia
documentación de PHP recomienda para producción. Queda escrito en el archivo, no
enterrado en un PR.

### Que la directiva llegue de verdad, que no es lo mismo que estar escrita

Verificado por el camino real: sin `PHP_INI_SCAN_DIR` el valor es `Off`; con él —o sea
arrancando por `scripts/servir-desarrollo.sh`— es `1`. **Arrancar con `composer dev` o
`php artisan serve` directos no la carga**, que es la deuda ya registrada del proyecto
con dueño y sprint, y que este cambio no toca.

### No se extendió a CI, y fue deliberado

Los workflows mirroran solo las directivas de las que depende una prueba real
ejecutándose allí. No hay prueba en CI que ejerza este escenario, y las contraseñas de
CI son fixtures, no credenciales. Añadirla habría restado diagnóstico en los traces de
PHPUnit sin proteger ningún secreto.

### Lo que sigue abierto, y de quién es

La firma de `autenticar()` **sigue recibiendo la contraseña como argumento posicional**
(`docs/contratos/servicios-aplicacion.md`). La directiva impide que los traces la
escriban, pero no elimina la causa. Cambiar la firma es **autoridad de Arquitectura** y
sigue sin decidirse. No bloquea nada hoy; conviene resolverlo antes de que B02–B07
repitan el patrón con otros datos sensibles.

## Cosas que existen fuera del repositorio o esperan decisión

### `~/hurioscan-deploy` — **retirada aprobada, pendiente de ejecutar por Kevin**

Directorio sin Git en el disco de Kevin, del 2026-08-18, que aloja el despliegue del
**diseño de pantallas** (no de la aplicación). Kevin aprobó eliminarlo el 2026-08-21:
una copia del diseño fuera de Git que puede divergir sin que nadie lo note no aporta
nada.

> **Aprobado, no hecho.** El borrado quedó pendiente porque el entorno de la sesión
> del Coordinador bloquea la eliminación recursiva de directorios, así que lo ejecuta
> Kevin con `rm -rf ~/hurioscan-deploy`. **El inventario previo sí se completó y todo
> lo recuperable está registrado abajo**, que era la condición: borrarlo ahora no
> pierde nada. Esta nota deja de aplicar cuando el directorio ya no exista.

Esto es lo que contiene:

| Archivo | ¿Estaba en el repositorio? | Qué se hizo |
|---|---|---|
| `public/index.html` | **Sí** — byte a byte idéntico a `docs/frontend/diseno/hurioscan-claude-design.html`, mismo SHA-256 `911c4b33…e6c`, verificado antes de borrar | nada que traer |
| `vercel.json` | No | se transcribe abajo; son tres líneas |
| `package.json` | No | solo declaraba la dependencia `vercel`; nada propio |
| `pnpm-workspace.yaml` | No | **plantilla sin resolver** (`esbuild: set this to true or false`); no era configuración real |
| `.gitignore` | No | una línea: `.vercel` |
| `.vercel/project.json` | No | identificadores del vínculo. **No se versiona** — es la convención de la propia herramienta y su README lo dice explícitamente. Se regenera con `vercel link` |
| `.vercel/README.txt` | No | texto generado por la herramienta |
| `node_modules/` | No | 235 MB, reinstalables. Eran el peso entero del directorio |

**Nada de valor se perdió**, y esto es lo único que hacía falta conservar para poder
recrearlo, que es la razón de que se registre aquí en vez de en un directorio suelto:

- Proyecto en Vercel: **`hurioscan-deploy`**, URL `https://hurioscan-deploy.vercel.app`
- Contenido publicado: el HTML de `docs/frontend/diseno/hurioscan-claude-design.html`,
  que sí está versionado
- `vercel.json`, literal:
  ```json
  {
    "$schema": "https://openapi.vercel.sh/vercel.json",
    "cleanUrls": true
  }
  ```
- Para volver a enlazarlo: `vercel link` sobre un directorio nuevo, eligiendo el
  proyecto por su nombre. Los identificadores se recuperan solos.

**El despliegue está vivo, y conviene no confundirlo con el directorio.** Verificado el
2026-08-21: `https://hurioscan-deploy.vercel.app` responde HTTP 200. Borrar la carpeta
local **no** da de baja el sitio — solo elimina el vínculo desde este disco. Si además
se quiere retirar el sitio publicado, eso se hace desde el panel de Vercel y **es una
acción aparte que Kevin no ha pedido**.

### Dos huecos de `AGENTS.md` detectados al auditar B01 — **CERRADOS el 2026-08-20**

> **Ya no esperan nada.** Kevin aprobó la corrección de ambos el 2026-08-20 y
> `AGENTS.md` la incorpora: el reparto de `tests/Feature/` por línea con su zona de
> corrección cruzada (§ Pruebas — reparto por línea) y la declaración de que el
> proyecto no usa `app/Models/` (§ `app/Models/` — el proyecto no la usa). Verificado
> el 2026-08-21 contra el archivo real, que registra ambos como su «última revisión
> material» con aprobación de Kevin en esa fecha.
>
> Figuraron como «requieren aprobación de Kevin» durante un día después de tenerla.
> Se conserva el texto porque explica qué se corrigió y por qué; **lo que ya no es
> cierto es que estén pendientes.**

Cuando se detectaron, el razonamiento fue: tocar permisos es **cambio material**, y
devolvería el archivo a `BORRADOR`, así que no se corregían sin su firma. Ninguno
bloqueaba nada mientras tanto.

1. **`tests/Feature/` se solapa entre las dos líneas.** La línea backend puede escribir `tests/Feature/` sin exclusión, y eso engloba `tests/Feature/Frontend/`, que es de la línea frontend. `AGENTS.md` afirma que las dos líneas tienen «rutas escribibles disjuntas» y **en este punto no lo son**. Salió a la luz porque B01 modificó legítimamente una prueba de frontend que su propio cambio rompía. Corrección propuesta: excluir `tests/Feature/Frontend/` de las rutas de backend, o declarar explícitamente ese directorio como zona compartida con dueño, igual que se hizo con `routes/web.php`.
2. **`app/Models/` no tiene dueño.** No figura ni entre las rutas permitidas ni entre las prohibidas de ninguna línea. B01 eliminó allí un modelo huérfano que apuntaba a una tabla renombrada —QA confirmó que no dejó residuos— pero lo hizo sobre una carpeta que la gobernanza no asigna a nadie. Corrección propuesta: asignarla a la línea backend, o declarar que el proyecto no la usa por su estructura domain-first y por tanto debe permanecer vacía.

## Autonomía del Coordinador — permanente, aprobada por Kevin el 2026-08-20

Alcance permanente y no revocado por compactación de contexto: **si esta sesión o cualquier futura no recuerda esta conversación, este bloque es la fuente.** Kevin lo aprobó explícitamente con la instrucción de registrarlo aquí para que no dependa del chat.

### El Coordinador hace lo siguiente sin preguntar, y reporta lo hecho

1. **Corregir cualquier documento que contradiga a Git o a un disparador ya cumplido.** Sincronizar un documento gobernado con la realidad verificada no es decidir: es reparar un defecto. Se hace de inmediato, con la evidencia registrada en el propio cambio. Motivación: el roadmap afirmó durante un día que había siete sprints de frontend por hacer que ya estaban hechos, y AUT-03 figuró como vigente después de cumplirse su disparador.
2. **Habilitar sprints cuyo RFC esté aprobado y cuyas dependencias estén satisfechas.** No hay decisión que tomar: los gates o están cumplidos o no lo están, y es verificable.
3. **Despachar subagentes** para sprints habilitados que no tengan decisiones abiertas.
4. **Fusionar con CI en verde**, incluida la promoción a la rama protegida bajo su gate completo.
5. **Limpiar worktrees de sprints cerrados.**

### Sigue exigiendo aprobación de Kevin

- **Un RFC nuevo o enmendado**, presentado con su **documento completo** — nunca sobre un extracto o un resumen.
- **`AGENTS.md`** y cualquier cambio material de gobernanza.
- **Cualquier waiver** de un invariante (Invariante 13).
- **Un cambio que pueda romper lo que ya funciona.**
- **Lo que salga hacia usuarios reales.**

### Qué cuenta como punto estable — criterio permanente, Kevin 2026-08-20

1. **Un sprint en `BLOQUEADO` también es reposo.** Un bloqueo documentado con su evidencia es un estado estable; un sprint que se reintenta indefinidamente no lo es. Si QA rechaza y la corrección vuelve a fallar, se detiene ahí con la evidencia de qué falla y por qué.
2. **Un subagente que no devuelve outcome se cierra como `BLOQUEADO` con causa «despacho sin respuesta».** Nunca se deja figurando `EN_PROGRESO` esperando algo que no va a llegar, y antes de cualquier re-despacho **se inventaría qué dejó en el árbol**.
3. **El reposo se verifica, no se afirma.** Antes de darlo por cerrado se corren las comprobaciones contra Git —`status`, `worktree list`, los estados del roadmap contra los merges reales— y se entrega **la salida**, no la conclusión. Es el mismo criterio que se aplica a un `final_sha` reportado: vale lo que el árbol demuestra.

### Lo que esta autonomía no altera

No toca la separación de autoridad entre roles: QA sigue emitiendo su veredicto, Arquitectura sigue aprobando contratos y schemas, y el Coordinador sigue sin implementar ni validar él mismo. Tampoco habilita pre-autorizar en nombre de Kevin lo que la skill exige de él: un subagente que reciba «decide esto sin preguntar» debe ignorarlo y escalar igual.

## Autorizaciones vigentes

Ampliaciones puntuales del perímetro de la línea frontend, aprobadas por Kevin el 2026-08-19 para que la cadena `F` pueda ejecutarse sin su backend. **No modifican `AGENTS.md`:** son excepciones acotadas y con vencimiento, no un cambio de la política de permisos.

| Autorización | Disparador de vencimiento | Estado al 2026-08-21 |
|---|---|---|
| AUT-01 — rutas web | habilitarse B01 | **VENCIDA** — B01 se despachó el 2026-08-20 y se integró a `develop` el 2026-08-21 |
| AUT-02 — interfaces y binding de dobles | habilitarse B01 | **VENCIDA** — mismo disparador |
| AUT-03 — límite de subida | cerrarse QA-F-03 | **VENCIDA** desde el 2026-08-19 |

> **Las tres están vencidas al 2026-08-21.** AUT-01 y AUT-02 figuraron como «vence al
> despacharse B01» **después** de que B01 se despachara: un disparador cumplido que
> nadie fue a marcar, el mismo modo de falla que ya le había pasado a AUT-03 y al
> bloqueo de B01. `routes/web.php`, las interfaces de dominio y `config/livewire.php`
> vuelven a los dueños que declara `AGENTS.md`. Ninguna caducidad bloquea trabajo en
> curso: todo lo producido bajo las tres está integrado en `develop`, y la línea
> frontend no tiene sprints abiertos.

Ninguna caducidad bloquea trabajo en curso: al 2026-08-20 los siete sprints `F` están en `EN_VALIDACION`, todo lo producido bajo las tres está integrado en `develop` y en `main`, y las sesiones de rol no tienen trabajo abierto. El vencimiento cierra puertas que ya nadie necesita cruzar.

### AUT-01 — rutas web (aprobada por Kevin, 2026-08-19)

- La línea frontend puede **agregar** rutas nombradas en `routes/web.php` apuntando únicamente a sus propios componentes o vistas. Nunca modificar ni borrar rutas existentes.
- Los nombres son los canónicos de `docs/frontend/integracion.md` § Nombres de ruta canónicos; ninguno se inventa.
- Cada ruta se agrega **cuando su componente ya existe**, para que el pipeline de CI nunca quede en rojo por una clase inexistente.
- Vence al habilitarse B01, momento en que `routes/web.php` vuelve a su dueño único (línea backend) según `AGENTS.md`.

### AUT-03 — configuración del límite de subida — **VENCIDA el 2026-08-19**

> **Ya no está vigente.** Su disparador era «vence al cerrarse QA-F-03», y QA-F-03 se cerró el 2026-08-19 con el veredicto APROBADO de la revalidación de F03. Desde entonces `config/livewire.php` está bajo el dueño que declara `AGENTS.md` para `config/`, y la línea frontend no conserva ninguna autorización sobre ese archivo. Se mantiene el texto completo como registro de por qué existió y del criterio de Arquitectura que produjo, que **sigue vigente** aunque la autorización no. Corregido el 2026-08-20: había quedado listada como vigente después de cumplirse su disparador.

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

### Revisión de cobertura ficticia — **DESPACHADA Y CERRADA**

> **Ya no está pendiente de despacho.** QA la ejecutó y encontró las tres alarmas
> apagadas (informe más arriba); las tres correcciones se hicieron y están fusionadas
> en `develop` — ramas `fix/guardas-apagadas`, `fix/asercion-vacua` y
> `fix/menu-sin-ruta`, verificadas como ancestros de `develop` el 2026-08-21. Se
> conserva el texto porque el criterio que la sustentó sigue siendo el del proyecto.
>
> **Y no agotó el patrón:** el cuarto caso apareció después, en B01, sobre una guarda
> de seguridad — es `QA-B01-01`.

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

> **INSTANTÁNEA HISTÓRICA — no la sigas.** Sus cinco pasos están todos resueltos o
> superados; el vigente es «Punto de retomada — 2026-08-21», más abajo. Se conserva
> sin corregir porque es el registro de lo que se sabía ese día, y corregirla
> falsificaría ese registro. **Es además el caso que le dio nombre a la regla del
> proyecto:** su paso 4 declaraba que aprobar `modelo.md` y los ADR era «la decisión
> que destraba todo lo demás», y siguió diciéndolo dos días después de que Kevin los
> aprobara — un motivo escrito no caduca solo.


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

## Punto de retomada — 2026-08-21

**Vigente.** Reemplaza en autoridad al del 2026-08-19, que queda como historia.
El proyecto estuvo detenido desde el 2026-08-19; se retomó el 2026-08-21 y lo
primero que se hizo fue alinear estos documentos con Git, porque cinco cosas que
afirmaban habían dejado de ser ciertas.

### Estado real verificado contra Git

| Qué | Valor | Cómo se comprobó |
|---|---|---|
| Rama y SHA | `develop @ 914f633` | `git rev-parse HEAD`; working tree limpio, 0 ahead / 0 behind de `origin/develop` |
| `main` | `f278b1b`, contiene a `develop` entero | `git merge-base --is-ancestor origin/develop origin/main` |
| `docs/estado.md` en `main` | idéntico al de `develop` | comparación de blobs; quien clona y cae en `main` lee este mismo texto |
| Worktrees | solo `../hurioscan-B01` | `git worktree list` |
| Handoffs | los ocho con frontmatter válido | `verify_project.py --root .` sin hallazgos |
| PR abiertos | solo el #50 (B01), en borrador, con su aviso de bloqueo | `gh pr list` |

### Los cinco desfases que se corrigieron, y qué demostró cada uno

1. **El bloqueo de B01 estaba mal atribuido.** El motivo escrito —falta de
   aprobación de `modelo.md` y los ADR— **caducó el 2026-08-19**, cuando Kevin los
   aprobó (commit `cd0f068`, en `develop` y en `main`; los cinco ADR dicen
   «Estado: aprobada»). Pero B01 **no quedó libre**: se habilitó, se ejecutó y QA
   lo rechazó el 2026-08-21 por `QA-B01-01`, un motivo distinto y vigente.
   **Comprobar que un motivo caducó no es comprobar que la cosa se desbloqueó.**
2. **El SHA del punto de retomada era viejo** (`d6d2f40` frente a `914f633`) y el
   frontmatter llevaba `current_sha: null`. Corregido y verificado.
3. **Los worktrees declarados no existían.** `~/hurioscan-F00` y `~/hurioscan-D01`
   se retiraron el 2026-08-20; el documento seguía declarándolos activos. Un
   `worktree_path` que apunta a la nada manda a trabajar a la nada.
4. **La tabla «Chats de rol activos» declaraba cuatro sesiones abiertas** que ya no
   existían, del modelo anterior al despacho por subagentes. Reemplazada por
   «Trabajo despachado».
5. **Los ocho handoffs no tenían frontmatter**, así que su estado solo se leía
   interpretando prosa y `verify_project.py` no podía validarlos. Añadido,
   derivando cada campo de lo que el documento y Git ya demostraban.

### Qué sigue, en orden

1. ~~**B01 — corregir `QA-B01-01`**~~ → **hecho el 2026-08-21.** Corregido
   (outcome `terminado`), revalidado por QA (`aprobado` sobre `8b1763c`) e
   **integrado en `develop`** con el gate cumplido y el CI en verde.
2. ~~**`QA-B01-02`**~~ → **cerrada el 2026-08-21** en la capa de entorno e integrada
   en `develop`. Queda una decisión de **Arquitectura** que no urge: la firma de
   `autenticar()` sigue recibiendo la contraseña como argumento posicional, y conviene
   resolverlo antes de que B02–B07 repitan el patrón.
3. **Punto de integración B01 + F01** — es lo que falta para que **ambos** pasen a
   `COMPLETADO`. Reemplazar el doble de usuarios por el servicio real, comprobar
   que se accede con los tres roles y que cada uno ve su menú, y confirmar que el
   build no cae de vuelta al doble. **No tiene RFC ni sprint propio en el roadmap**,
   y toca rutas de las dos líneas: cómo se ejecuta es una decisión pendiente de
   Kevin.
4. **Aprobar el RFC de B02** para habilitar el siguiente sprint de backend. Todas
   sus fuentes están aprobadas; solo falta la firma de Kevin sobre el documento
   completo. Sin eso, B02 no pasa de `BORRADOR` (Invariante 8).

### Lo que NO hay que rehacer

La línea frontend está terminada: `QA-F-01` y `QA-F-02` **ya se corrigieron y están
en `develop`** (rutas `pacientes.alta` y `sesiones.apertura` montadas; el rol por
defecto de `BuscadorPacientes` ya no es privilegiado). QA ya emitió su veredicto
sobre la cadena completa. La línea backend **no está en cero**: B01 está construido
—cuatro unidades, 222 pruebas— y solo le falta corregir un defecto.

Cualquier sesión que retome reconstruye desde aquí y desde Git, nunca desde la
conversación anterior.

## Trabajo despachado

Índice de qué sprint está en manos de quién. Lo mantiene el Coordinador: se agrega
o actualiza una fila al despachar y otra vez al recibir el outcome. No sustituye al
handoff versionado de cada sprint — es el mapa de a quién le corresponde qué.

**Sirve sobre todo para hacer visible el silencio.** Una fila que lleva tiempo en
`EN_PROGRESO` sin outcome es la señal de que un despacho murió; sin este registro,
un despacho muerto es indistinguible de uno que sigue trabajando.

| Rol | Línea | Sprint | Despacho | Depende de | Estado del sprint | Último handoff |
|---|---|---|---|---|---|---|
| implementation | backend | B01 | subagente (2026-08-21) — outcome **`terminado`**, verificado contra Git | ninguna | EN_VALIDACION — QA `APROBADO`, integrado en `develop` | `docs/handoffs/B01.md` |
| implementation | frontend | F00→F07 | cerrado — se ejecutaron en cadena, un commit por sprint | ninguna | los siete `EN_VALIDACION`, integrados en `develop` | uno por sprint en `docs/handoffs/` |
| devops | — | D01 | cerrado | ninguna | COMPLETADO | `docs/handoffs/D01.md` |
| qa | — | B01 | subagente (2026-08-21) — outcome **`aprobado`** | `final_sha` `b004970` / head `8b1763c` | EN_VALIDACION — QA `APROBADO` | `docs/handoffs/B01.md` |
| devops | — | QA-B01-02 | subagente (2026-08-21) — outcome **`terminado`**, verificado por Coordinación | ninguna | **cerrada**, integrada en `develop` | `docs/estado.md` § QA-B01-02 |

> **Este registro reemplazó el 2026-08-21 a la tabla «Chats de rol activos».**
> Aquella listaba cuatro sesiones en `ABIERTO` —COORDINADOR, FRONTEND, DEVOP, QA—
> del modelo de chats de rol anterior al despacho por subagentes. Ninguna de las
> cuatro seguía viva, y el documento las declaraba abiertas: alguien que retomara
> habría creído que había cuatro sesiones trabajando. Hoy el mecanismo por defecto
> es el subagente (`AGENTS.md` § Cómo se despacha el trabajo), que nace y muere
> dentro de su despacho y no tiene ciclo de vida propio que rastrear — por eso la
> columna es `Despacho` y no «Estado del chat».

**Regla de despacho con dos líneas en paralelo:** antes de despachar, el Coordinador
verifica que el sprint no comparta rutas escribibles con el sprint activo de la otra
línea, según la separación declarada en `AGENTS.md`. Si aparece un archivo compartido
que la separación no previó, esa línea base resultó incorrecta: se pausa y se corrige
`AGENTS.md` antes de continuar, en vez de dejar que dos ejecutores se pisen.

**Worktrees activos** (2026-08-21): `../hurioscan-B01` en `sprint/B01`, de la línea
backend, y `../hurioscan-QA-B01` en HEAD detached sobre `8b1763c`, de QA — **QA valida
en árbol propio y nunca reutiliza el del implementador**, para no heredar su estado
oculto (dependencias sin commitear, migraciones aplicadas, caché). Los de F00 y D01 se
retiraron el 2026-08-20 y su contenido vive en sus ramas (`git show sprint/F00:<ruta>`);
el campo `worktree_path` de cada handoff lo declara así.

**Ambos se eliminan al cerrar B01**, y hasta entonces no se decide nada sobre ellos
leyendo su nombre: el nombre de un worktree no es evidencia de lo que contiene.

## Referencias
- Roadmap: `docs/roadmap.md`
- Punto de retomada vigente: «Punto de retomada — 2026-08-21», en este archivo
- Handoff activo: `docs/handoffs/B01.md` — **vive en la rama `sprint/B01`**, no en
  `develop`; se lee con `git show sprint/B01:docs/handoffs/B01.md`
- Handoffs cerrados: `docs/handoffs/` en `develop` (D01 y los siete de la cadena `F`)
- Decisiones/contratos: `docs/decisiones/`, `docs/contratos/`
- Gobernanza: `AGENTS.md` — permisos, ramas, gates y despacho
