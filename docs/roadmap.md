# Roadmap — HuriosCan

Horizonte: los RF-001 a RF-015 aprobados en `docs/requisitos/rf.md`. No incluye la fase 2 (extracción de campos estructurados) ni ninguna capacidad marcada fuera del horizonte.

Estado de la planificación: **BLOQUEADA** — ningún sprint puede pasar a `Planificación: LISTO` mientras `AGENTS.md` siga en `BORRADOR` y los RF, contratos, schema, taxonomía de errores y permisos sigan en `propuesto`. **Excepción: F00**, que no consume ningún contrato ni requisito funcional y solo depende del diseño ya producido.

## Estructura del trabajo

El proyecto separa **sprints de backend (`B`)** y **sprints de frontend (`F`)**, de modo que la interfaz pueda avanzar sin esperar al servidor.

Eso es posible porque **los 27 contratos ya están escritos**: cada operación tiene su ruta, sus validaciones campo por campo, su respuesta y sus códigos de error. Un sprint de frontend consume esa definición, no la implementación.

Mientras su backend no exista, cada sprint de frontend trabaja contra un **doble de desarrollo**: una implementación de la firma del servicio que devuelve datos fijos, activada por configuración y acotada a los entornos local y de pruebas. Nunca vive dentro del flujo productivo.

**Consecuencia que hay que aceptar explícitamente:** un sprint de frontend terminado contra su doble llega hasta `EN_VALIDACION`, no hasta `COMPLETADO`. Para cerrarse necesita la integración real con su sprint de backend: reemplazar el doble por el servicio verdadero y verificar el flujo completo. Ese punto de integración está declarado abajo y es trabajo real, no un trámite — es el costo de trabajar en paralelo.

## Matriz de cobertura

| Fuente | Backend | Frontend |
|---|---|---|
| RF-001 registro y búsqueda de pacientes | B02 | F02 |
| RF-002 apertura de sesión por folder | B03 | F03 |
| RF-003 captura de hojas | B03 | F03 |
| RF-004 extracción automática de texto | B04 | — (sin pantalla propia; su resultado se ve en F05) |
| RF-005 revisión y corrección | B05 | F05 |
| RF-006 cierre de sesión | B05 | F05 |
| RF-007 búsqueda por contenido | B06 | F06 |
| RF-008 línea de tiempo del paciente | B06 | F06 |
| RF-009 visualización de documento | B06 | F06 |
| RF-010 panel de avance | B07 | F07 |
| RF-011 autenticación y roles | B01 | F01, F07 |
| RF-012 auditoría de accesos | B07 | F07 |
| RF-013 reanudación de sesiones pendientes | B03 | F03 |
| RF-014 cola de hojas ilegibles | B05 | F05 |
| RF-015 autocompletado del paciente por DNI | B02 | F02 |
| RNF-001 rendimiento de búsqueda | B06 | — |
| RNF-002 captura no bloqueante | B04 | — |
| RNF-003 imagen original inmutable | B03 | — |
| RNF-004 responsive en cuatro anchos | — | F00, F01, F03, F05, F06 |
| RNF-005 instantes en UTC | B01 | — |
| RNF-006 retención de auditoría | B07 | — |
| RNF-010 validación de input | todos los `B` | replicada en `F` para UX, sin sustituir al backend |
| RNF-011 queries parametrizadas | B06 | — |
| RNF-012 codificación de salida | — | F05, F06 (la codificación ocurre donde se renderiza) |
| RNF-013 deny-by-default | B01 establece el mecanismo; cada `B` prueba sus filas | F01 oculta lo no permitido, **sin sustituir la validación del backend** |
| RNF-014 sin secretos ni datos expuestos | B04, B07 | — |
| MIG-001 a MIG-008 | B01 a B05 | — |
| MIG-009 permisos sobre auditoría | B07 | — |
| Integración con motor de OCR | B04 | — |
| Integración con JSON.pe | B02 | — |
| Sistema de componentes de interfaz | — | F00 |
| Pipeline de CI declarado en `AGENTS.md` § CI por rama | D01 | — |
| Comandos de verificación de `AGENTS.md` | D01 | — |
| Despliegue, artefactos y ambientes | no aplica — el proyecto no tiene producción comprometida y `AGENTS.md` declara el proveedor como no decidido | — |
| Validación de cada sprint | QA — ver «Cuándo entra QA» abajo | — |

Ningún RF del horizonte queda sin sprint; ningún sprint existe sin una fuente que lo justifique.

## Sprints

| ID | Rol | Resultado observable | Depende de | Paralelizable con | RFC | Planificación | Ejecución |
|---|---|---|---|---|---|---|---|
| D01 | devops | Flujos de verificación en cada pull request hacia `develop` y `main` | ninguna | F00, B01 | `docs/rfcs/D01.md` | BORRADOR | PLANIFICADO |
| F00 | implementation | Componentes Blade reutilizables con sus variantes y su catálogo | ninguna | B01, D01 | `docs/rfcs/F00.md` | **LISTO** | **LISTO** |
| B01 | implementation | Se accede al sistema con usuario y rol; tablas base y deny-by-default | ninguna | F00 | `docs/rfcs/B01.md` | BORRADOR | PLANIFICADO |
| F01 | implementation | Pantalla de acceso, layout y menú por rol | F00 | B01, B02 | `docs/rfcs/F01.md` | BORRADOR | PLANIFICADO |
| B02 | implementation | Alta y búsqueda de pacientes; consulta de DNI al proveedor | B01 | F01, F02 | `docs/rfcs/B02.md` | BORRADOR | PLANIFICADO |
| F02 | implementation | Búsqueda de pacientes y alta con autocompletado | F00, F01 | B02, B03 | `docs/rfcs/F02.md` | BORRADOR | PLANIFICADO |
| B03 | implementation | Sesión de lote, captura y almacenamiento de imágenes | B02 | F02, F03 | `docs/rfcs/B03.md` | BORRADOR | PLANIFICADO |
| F03 | implementation | Pantalla de captura con las tres vías y sesiones pendientes | F00, F01 | B03, B04 | `docs/rfcs/F03.md` | BORRADOR | PLANIFICADO |
| B04 | implementation | OCR en segundo plano con motor intercambiable | B03 | F03, F05 | `docs/rfcs/B04.md` | BORRADOR | PLANIFICADO |
| F05 | implementation | Pantalla de revisión, marcado y cierre | F00, F01, F03 | B04, B05 | `docs/rfcs/F05.md` | BORRADOR | PLANIFICADO |
| B05 | implementation | Corrección con control de versión, transiciones y cierre | B04 | F05, F06 | `docs/rfcs/B05.md` | BORRADOR | PLANIFICADO |
| F06 | implementation | Búsqueda por contenido, línea de tiempo y visor | F00, F01 | B05, B06, F07 | `docs/rfcs/F06.md` | BORRADOR | PLANIFICADO |
| B06 | implementation | Índice de texto completo y entrega controlada de imágenes | B05 | F06, F07 | `docs/rfcs/B06.md` | BORRADOR | PLANIFICADO |
| F07 | implementation | Panel de avance, usuarios y auditoría | F00, F01 | B06, B07, F06 | `docs/rfcs/F07.md` | BORRADOR | PLANIFICADO |
| B07 | implementation | Agregados reales, auditoría append-only y gestión de usuarios | B05 | F07 | `docs/rfcs/B07.md` | BORRADOR | PLANIFICADO |

## Cuándo entra QA

**QA no tiene sprint propio, y es deliberado.** Un sprint de QA sería un sprint sin resultado propio: la validación no produce código ni documentos de línea base, produce un veredicto sobre el trabajo de otro sprint.

QA entra cuando **un sprint pasa a `Ejecución: EN_VALIDACION`**, y valida contra los criterios de cierre de la tabla de unidades de trabajo de ese RFC — no contra un criterio propio ni contra su interpretación del requisito.

| Qué | Dónde vive |
|---|---|
| Cuándo trabaja | Cuando un sprint entra en `EN_VALIDACION` (ver `docs/estado.md`) |
| Contra qué valida | Los criterios de cierre del RFC de ese sprint |
| Sobre qué objeto | El `final_sha` exacto que reportó Implementación, en su propio checkout o worktree |
| Qué produce | Veredicto `APROBADO` / `RECHAZADO` / `BLOQUEADO`; el Coordinador lo registra en el handoff |
| Qué no hace | No escribe código, no commitea durante la validación, no hace merge, no cierra el sprint |

**Un chat que se declare `qa` cuando ningún sprint está en `EN_VALIDACION` no tiene trabajo habilitado**: lo reporta y se detiene, igual que cualquier otro rol sin sprint asignado.

El gate está declarado en `AGENTS.md`: «QA APROBADO sobre ese `final_sha` cuando el sprint requiere QA».

## Puntos de integración

Un par `B`/`F` no está cerrado hasta que se integran de verdad. Cada punto exige: reemplazar el doble por el servicio real, verificar el flujo de punta a punta y confirmar que el build no cae de vuelta al doble.

| Integración | Verifica |
|---|---|
| B01 + F01 | Se accede con los tres roles y cada uno ve su menú |
| B02 + F02 | Se registra un paciente real, con y sin autocompletado por DNI |
| B03 + F03 | Se capturan hojas reales por las tres vías y se guardan |
| B04 + B05 + F05 | Una hoja capturada produce texto, se corrige y se cierra el folder |
| B06 + F06 | Se busca una palabra y aparece el documento que la contiene |
| B07 + F07 | El panel muestra el avance real de lo digitalizado |

## Qué se puede empezar hoy

**F00 es el único sprint sin ninguna dependencia**, ni de código ni de aprobación: no consume contratos ni requisitos funcionales, solo el diseño ya producido. Puede ejecutarse de inmediato.

Los demás sprints de frontend dependen además de que **su contrato esté aprobado** — no de que el backend exista, pero sí de que la definición sea estable. Construir una pantalla contra un contrato que puede cambiar es rehacerla dos veces.

## Correspondencia con el cronograma de la propuesta

La fase 2 (semanas 4–6) cubre F00, B01, F01 y B02. La fase 3 (semanas 7–10) cubre F02 a B06 con los pares avanzando en paralelo. La fase 4 (semanas 11–12) cubre F07, B07 y los puntos de integración pendientes.

**Reparto entre los cinco integrantes:** los dos programadores toman una línea cada uno —uno backend, otro frontend— y se encuentran en los puntos de integración. Es lo que esta separación habilita y la razón por la que se adoptó.
