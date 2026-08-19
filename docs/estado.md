---
project: HuriosCan
source_status: CANONICA
baseline: línea base parcialmente aprobada — frontend habilitado; persistencia y ADR pendientes a propósito
active_phase: F00
active_status: EN_PROGRESO
last_completed_phase: null
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
    execution_status: EN_VALIDACION
    depends_on: []
    parallelizable_with: [F00, B01]
  - id: F00
    repository: hurioscan
    planning_status: LISTO
    execution_status: EN_PROGRESO
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
    execution_status: PLANIFICADO
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
    execution_status: PLANIFICADO
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
    execution_status: PLANIFICADO
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
    execution_status: PLANIFICADO
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
    execution_status: PLANIFICADO
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
    execution_status: PLANIFICADO
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
- 2026-08-19: **F00 avanzado** — UT-01 a UT-04 completas y la vista de UT-05, en `sprint/F00 @ 6024ee8`; pint passed, 19/19 tests, build ok, responsive verificado en 360/768/1024/1440. Falta materializar la ruta `/componentes` (cubierta por el waiver de rutas de la delegación) y cerrar el sprint.

## Bloqueantes
- **Persistencia y ADR sin aprobar (deliberado):** `docs/persistencia/modelo.md` y `docs/decisiones/` siguen en `propuesto`. La línea backend completa (B01–B07) queda `BORRADOR`/`PLANIFICADO` hasta que Kevin los apruebe. Ninguna delegación vigente permite habilitarlos.
- Decisiones abiertas registradas, ninguna bloquea lo aprobado pero sí la implementación del sprint que las consume:
  - formato de documento de identidad: si el establecimiento registra carné de extranjería además de DNI, el formato de 8 dígitos no alcanza. El proveedor elegido ofrece consulta de carné de extranjería como servicio aparte, pero se declaró fuera del horizonte: un paciente extranjero se registra a mano (B02);
  - activación de la cuenta de JSON.pe: los 100 créditos gratuitos vencen a los 30 días y el proyecto dura 12 semanas, así que la cuenta real se activa cerca de la demostración final, no ahora (B02);
  - límite de 15 MB por hoja: confirmar contra los equipos reales del establecimiento (B03);
  - motor de OCR de producción: lo decide el benchmark (B04).

## Siguiente fase habilitada
- **Línea frontend completa habilitada bajo delegación:** F00 (en curso) y F01, F02, F03, F05, F06, F07 (`Planificación: LISTO`). Su ejecución es secuencial en cadena — cada sprint queda habilitado al cerrar los criterios del anterior dentro de la delegación vigente — y termina en `EN_VALIDACION` (QA no disponible durante la delegación).
- **D01 en `EN_VALIDACION`:** pendiente veredicto QA y la integración del PR #18 a `develop` por el Coordinador.
- **Backend:** bloqueado hasta que Kevin apruebe persistencia y ADR.

## Delegación

### Delegación — aprobada por Kevin (VIGENTE)

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
- Estado: **VIGENTE**

### Bitácora de la delegación

Una fila por decisión que el usuario no vio antes de irse. Las decisiones tomadas dentro de cada sprint se registran además en su handoff; el Coordinador las consolida aquí al regreso de Kevin.

| # | Sprint | Qué se decidió | Alternativas descartadas | Por qué | Reversible | Dónde quedó |
|---|---|---|---|---|---|---|

## Chats de rol activos

Índice de qué sesión trabaja en qué. Lo mantiene el Coordinador: se agrega una fila al despachar un chat y se actualiza al recibir su handoff. No sustituye al handoff versionado de cada sprint — es el mapa de quién está haciendo qué.

| Rol | Línea | Sesión (chat principal) | Sprint activo | Depende de | Estado del chat | Estado del sprint | Último handoff |
|---|---|---|---|---|---|---|---|
| coordinacion | — | COORDINADOR (este chat) | — | — | ABIERTO | — | — |
| implementation | frontend | FRONTEND | cadena F00→F07 (delegada) | ninguna | ABIERTO | F00 EN_PROGRESO | `docs/handoffs/F00.md` @ sprint/F00 |
| implementation | backend | (sin despachar) | B01 | aprobación de persistencia/ADR | — | PLANIFICADO | — |
| devops | — | DEVOP | D01 | ninguna | ABIERTO | EN_VALIDACION | evidencia en reporte al Coordinador; PR #18 |
| qa | — | QA | D01 (por despachar) | D01 en EN_VALIDACION | ABIERTO | — | — |

> Worktrees activos: `../hurioscan-F00` en `sprint/F00` y `../hurioscan-D01` en `sprint/D01`, ambos desde `develop @ bb7ae5b`. Ver "Aislamiento del trabajo paralelo" en `AGENTS.md`. Los cuatro chats se comunican por mensajería directa entre sesiones (SendMessage); el Coordinador es COORDINADOR.

**Regla de despacho con dos líneas en paralelo:** antes de abrir un chat, el Coordinador verifica que su sprint no comparta rutas escribibles con el sprint activo de la otra línea, según la separación declarada en `AGENTS.md`. Si aparece un archivo compartido que la separación no previó, esa línea base resultó incorrecta: se pausa y se corrige `AGENTS.md` antes de continuar, en vez de dejar que dos agentes se pisen.

## Referencias
- Roadmap: `docs/roadmap.md`
- Handoff activo: `docs/handoffs/F00.md` (en rama `sprint/F00`)
- Decisiones/contratos: `docs/decisiones/`, `docs/contratos/`
