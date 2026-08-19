---
project: HuriosCan
source_status: CANONICA
baseline: línea base parcialmente aprobada — frontend habilitado; persistencia y ADR pendientes a propósito
active_phase: cadena F00→F07
active_status: EN_VALIDACION
last_completed_phase: D01
bootstrap_status: COMPLETO
planning_horizon_status: PARCIAL — línea frontend LISTO; línea backend BLOQUEADA
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
- **Persistencia y ADR sin aprobar (deliberado):** `docs/persistencia/modelo.md` y `docs/decisiones/` siguen en `propuesto`. La línea backend completa (B01–B07) queda `BORRADOR`/`PLANIFICADO` hasta que Kevin los apruebe. Ninguna delegación vigente permite habilitarlos.
- Decisiones abiertas registradas, ninguna bloquea lo aprobado pero sí la implementación del sprint que las consume:
  - formato de documento de identidad: si el establecimiento registra carné de extranjería además de DNI, el formato de 8 dígitos no alcanza. El proveedor elegido ofrece consulta de carné de extranjería como servicio aparte, pero se declaró fuera del horizonte: un paciente extranjero se registra a mano (B02);
  - activación de la cuenta de JSON.pe: los 100 créditos gratuitos vencen a los 30 días y el proyecto dura 12 semanas, así que la cuenta real se activa cerca de la demostración final, no ahora (B02);
  - límite de 15 MB por hoja: confirmar contra los equipos reales del establecimiento (B03);
  - motor de OCR de producción: lo decide el benchmark (B04).

## Siguiente fase habilitada
- **Línea frontend en cadena:** F00 (en curso) y F01, F02, F03, F05, F06, F07 (`Planificación: LISTO`), ejecutados secuencialmente en `sprint/F00` por la sesión FRONTEND. Cada sprint cierra con su commit y su handoff antes de pasar al siguiente; la cadena termina en `EN_VALIDACION` y QA la valida después.
- **D01:** `COMPLETADO` e integrado. Todo PR hacia `develop` ejecuta ahora lint, tests y build automáticamente.
- **Backend:** bloqueado hasta que Kevin apruebe `docs/persistencia/modelo.md` y los ADR.

## Autorizaciones vigentes

Ampliaciones puntuales del perímetro de la línea frontend, aprobadas por Kevin el 2026-08-19 para que la cadena `F` pueda ejecutarse sin su backend. **No modifican `AGENTS.md`:** son excepciones acotadas y con vencimiento, no un cambio de la política de permisos.

### AUT-01 — rutas web (aprobada por Kevin, 2026-08-19)

- La línea frontend puede **agregar** rutas nombradas en `routes/web.php` apuntando únicamente a sus propios componentes o vistas. Nunca modificar ni borrar rutas existentes.
- Los nombres son los canónicos de `docs/frontend/integracion.md` § Nombres de ruta canónicos; ninguno se inventa.
- Cada ruta se agrega **cuando su componente ya existe**, para que el pipeline de CI nunca quede en rojo por una clase inexistente.
- Vence al habilitarse B01, momento en que `routes/web.php` vuelve a su dueño único (línea backend) según `AGENTS.md`.

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

## Punto de retomada — pausa del 2026-08-19

Sesión pausada por ausencia de Kevin. **Todo el trabajo está persistido en `origin`**: ningún worktree tenía cambios sin commitear y ninguna rama local commits sin empujar, verificado antes de cortar. Nada quedó vivo solo en la memoria de una sesión.

- **Estado del árbol:** `develop @ d6d2f40`, con `pint` passed, 103/103 tests y las catorce rutas montadas.
- **Worktrees conservados:** `~/hurioscan-F00` en `sprint/F00 @ adabd5b` y `~/hurioscan-D01` en `sprint/D01 @ 9e9a839`, ambos limpios y ya integrados. El de D01 sigue pendiente de una decisión de Kevin sobre si se elimina.
- **En curso al pausar:** QA validaba la cadena `F00`→`F07` sobre `develop @ d6d2f40`. Su rol no escribe artefactos, así que lo perdido es solo su progreso de validación; el despacho sigue vigente tal cual y se rehace desde el repositorio.

### Qué sigue, en orden

1. **Retomar la validación de QA** sobre `develop @ d6d2f40`, con el mismo alcance: los siete sprints en `EN_VALIDACION`, validando sobre vistas montadas y con los interruptores de dobles activados en el entorno local. Los puntos a mirar con lupa están en la sección de auditoría de arriba.
2. **Con un `APROBADO`**, el Coordinador registra el veredicto en los handoffs. Los sprints `F` **no** pasan a `COMPLETADO` con eso: el roadmap exige el punto de integración con su par `B`, que todavía no existe.
3. **Decisión pendiente de Kevin, que es la que destraba todo lo demás:** aprobar `docs/persistencia/modelo.md` y los cinco ADR. Sin eso B01 no llega a `LISTO` y la línea backend sigue en cero — no hay modelos, servicios ni migraciones propias, solo las tres que Laravel trae de fábrica.
4. Al habilitarse B01 vencen **AUT-01** y **AUT-02**: `routes/web.php` y las interfaces vuelven a su dueño natural según `AGENTS.md`.

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
