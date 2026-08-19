---
project: HuriosCan
source_status: CANONICA
baseline: documentación inicial completa, sin aprobar
active_phase: F00
active_status: LISTO
last_completed_phase: null
bootstrap_status: COMPLETO
planning_horizon_status: BLOQUEADA
current_rfc_batch: [D01, F00, B01, F01, B02, F02, B03, F03, B04, F05, B05, F06, B06, F07, B07]
planning_scope: [RF-001, RF-002, RF-003, RF-004, RF-005, RF-006, RF-007, RF-008, RF-009, RF-010, RF-011, RF-012, RF-013, RF-014, RF-015]
updated_at: 2026-08-18
repositories:
  - name: hurioscan
    path: ~/hurioscan
    branch: develop
    current_sha: null
sprints:
  - id: D01
    repository: hurioscan
    planning_status: BORRADOR
    execution_status: PLANIFICADO
    depends_on: []
    parallelizable_with: [F00, B01]
  - id: F00
    repository: hurioscan
    planning_status: LISTO
    execution_status: LISTO
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
    planning_status: BORRADOR
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
    planning_status: BORRADOR
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
    planning_status: BORRADOR
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
    planning_status: BORRADOR
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
    planning_status: BORRADOR
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
    planning_status: BORRADOR
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

## Bloqueantes
- ~~`AGENTS.md` está en `BORRADOR`~~ → **APROBADO por Kevin el 2026-08-18**.
- Todos los documentos de línea base están en `propuesto`. Requieren aprobación del usuario real (requisitos, glosario, experiencia) y de Arquitectura (contratos, persistencia, errores, permisos, integración, ADR).
- Los quince RFC están en `propuesto`; ninguno puede pasar a `Planificación: LISTO` hasta que sus fuentes estén aprobadas. **Excepción: F00**, que no consume contratos ni requisitos funcionales y solo depende del diseño ya producido: puede aprobarse y ejecutarse de inmediato.
- Decisiones abiertas registradas, ninguna bloquea la aprobación de los documentos pero sí la implementación del sprint que las consume:
  - formato de documento de identidad: si el establecimiento registra carné de extranjería además de DNI, el formato de 8 dígitos no alcanza. El proveedor elegido ofrece consulta de carné de extranjería como servicio aparte, pero se declaró fuera del horizonte: un paciente extranjero se registra a mano (B02);
  - activación de la cuenta de JSON.pe: los 100 créditos gratuitos vencen a los 30 días y el proyecto dura 12 semanas, así que la cuenta real se activa cerca de la demostración final, no ahora (B02);
  - límite de 15 MB por hoja: confirmar contra los equipos reales del establecimiento (B03);
  - motor de OCR de producción: lo decide el benchmark (B04).

## Siguiente fase habilitada
- **F00 está habilitado** (`Planificación: LISTO`, `Ejecución: LISTO`): su RFC está aprobado y sus dos fuentes materiales —`docs/frontend/experiencia.md` y los RNF— también, tras detectarse que se había habilitado con ambas en `propuesto`. Para todo lo demás, el siguiente paso sigue siendo la aprobación de `AGENTS.md` y de la línea base documental; con eso, B01 y F01 quedan habilitados y las dos líneas —backend y frontend— pueden avanzar en paralelo.

## Chats de rol activos

Índice de qué sesión trabaja en qué. Lo mantiene el Coordinador: se agrega una fila al despachar un chat y se actualiza al recibir su handoff. No sustituye al handoff versionado de cada sprint — es el mapa de quién está haciendo qué.

| Rol | Línea | Sesión (chat principal) | Sprint activo | Depende de | Estado del chat | Estado del sprint | Último handoff |
|---|---|---|---|---|---|---|---|
| coordinacion | — | este chat | — | — | ABIERTO | — | — |
| implementation | frontend | (sin despachar) | F00 | ninguna | — | LISTO | — |

> Worktrees previstos al despachar: `../hurioscan-F00` en `sprint/F00` y `../hurioscan-B01` en `sprint/B01`, ambos desde `develop`. Ver "Aislamiento del trabajo paralelo" en `AGENTS.md`.
| implementation | backend | (sin despachar) | B01 | ninguna | — | PLANIFICADO | — |
| devops | — | (cerrado — se reabrirá con D01 aprobado) | D01 | aprobación de su RFC | CERRADO | PLANIFICADO | — |
| qa | — | (sin despachar) | ninguno | ningún sprint en EN_VALIDACION | — | — | — |

**Regla de despacho con dos líneas en paralelo:** antes de abrir un chat, el Coordinador verifica que su sprint no comparta rutas escribibles con el sprint activo de la otra línea, según la separación declarada en `AGENTS.md`. Si aparece un archivo compartido que la separación no previó, esa línea base resultó incorrecta: se pausa y se corrige `AGENTS.md` antes de continuar, en vez de dejar que dos agentes se pisen.

## Referencias
- Roadmap: `docs/roadmap.md`
- Handoff activo: ninguno todavía
- Decisiones/contratos: `docs/decisiones/`, `docs/contratos/`
