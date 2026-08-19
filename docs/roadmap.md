# Roadmap — HuriosCan

Horizonte: los RF-001 a RF-014 aprobados en `docs/requisitos/rf.md`. No incluye la fase 2 (extracción de campos estructurados) ni ninguna capacidad marcada fuera del horizonte.

Estado de la planificación: **BLOQUEADA** — ningún sprint puede pasar a `Planificación: LISTO` mientras `AGENTS.md` siga en `BORRADOR` y los RF, contratos, schema, taxonomía de errores y permisos sigan en `propuesto`. Esas aprobaciones son del usuario real y de Arquitectura; el Coordinador no puede emitirlas.

## Matriz de cobertura

| Fuente | Sprint que la cubre |
|---|---|
| RF-001 registro y búsqueda de pacientes | S02 |
| RF-002 apertura de sesión por folder | S03 |
| RF-003 captura de hojas | S03 |
| RF-004 extracción automática de texto | S04 |
| RF-005 revisión y corrección | S05 |
| RF-006 cierre de sesión | S05 |
| RF-007 búsqueda por contenido | S06 |
| RF-008 línea de tiempo del paciente | S06 |
| RF-009 visualización de documento | S06 |
| RF-010 panel de avance | S07 |
| RF-011 autenticación y roles | S01 |
| RF-012 auditoría de accesos | S07 |
| RF-013 reanudación de sesiones pendientes | S03 |
| RF-014 cola de hojas ilegibles | S05 |
| RNF-001 rendimiento de búsqueda | S06 — medición con seeder de 30 000 documentos |
| RNF-002 captura no bloqueante | S04 — test con motor de OCR demorado |
| RNF-003 imagen original inmutable | S03 — test de hash antes y después del ciclo completo |
| RNF-004 responsive en cuatro anchos | S03, S05, S06 — cada sprint verifica sus propias vistas |
| RNF-005 instantes en UTC | S01 — test de instante con zona de sesión alterada |
| RNF-006 retención de auditoría | S07 — MIG-009 revoca `UPDATE`/`DELETE` sobre `auditorias` |
| RNF-010 validación de input | todos los sprints, sobre sus propias operaciones |
| RNF-011 queries parametrizadas | S06 — test de inyección en el término de búsqueda |
| RNF-012 codificación de salida | S05 y S06 — test de payload XSS en texto extraído y en fragmento |
| RNF-013 deny-by-default | S01 establece el mecanismo; cada sprint prueba sus propias filas de la matriz |
| RNF-014 sin secretos ni datos personales expuestos | S04 y S07 — revisión de `LogError` y de mensajes de error |
| MIG-001 a MIG-008 | S01 a S05, según la entidad que introducen |
| MIG-009 permisos de base sobre auditoría | S07 |
| Integración con motor de OCR | S04 |

Ningún RF del horizonte queda sin sprint; ningún sprint existe sin una fuente que lo justifique.

## Sprints

| ID | Rol | Resultado observable | Fuentes | Depende de | Paralelizable con | RFC | Planificación | Ejecución |
|---|---|---|---|---|---|---|---|---|
| S01 | implementation | Se puede acceder al sistema con usuario y rol; las tablas base existen y el mecanismo de permisos rechaza por defecto | RF-011, RNF-005, RNF-013, MIG-001 | ninguna | ninguna | `docs/rfcs/S01.md` | BORRADOR | PLANIFICADO |
| S02 | implementation | Se registra un paciente y se lo encuentra por historia clínica, DNI o nombre | RF-001, MIG-002, `docs/contratos/pacientes.md` | S01 | ninguna | `docs/rfcs/S02.md` | BORRADOR | PLANIFICADO |
| S03 | implementation | Se abre la sesión de un folder, se capturan hojas por las tres vías y se retoma una sesión pendiente | RF-002, RF-003, RF-013, RNF-003, MIG-003, MIG-004, `docs/contratos/digitalizacion.md` | S02 | ninguna | `docs/rfcs/S03.md` | BORRADOR | PLANIFICADO |
| S04 | implementation | Una hoja capturada produce texto en segundo plano mediante el motor configurado, y un fallo se puede reintentar | RF-004, RNF-002, RNF-014, `docs/integraciones/ocr.md` | S03 | ninguna | `docs/rfcs/S04.md` | BORRADOR | PLANIFICADO |
| S05 | implementation | Se corrige el texto, se marca cada hoja y se cierra el folder con su resumen | RF-005, RF-006, RF-014, RNF-012, `docs/contratos/documentos.md` | S04 | ninguna | `docs/rfcs/S05.md` | BORRADOR | PLANIFICADO |
| S06 | implementation | Se busca por contenido y se consulta la línea de tiempo y el visor de un documento | RF-007, RF-008, RF-009, RNF-001, RNF-011, MIG-005 | S05 | S07 | `docs/rfcs/S06.md` | BORRADOR | PLANIFICADO |
| S07 | implementation | El panel muestra el avance real y el administrador consulta la auditoría, que la aplicación no puede alterar | RF-010, RF-012, RNF-006, MIG-006, MIG-007, MIG-009 | S05 | S06 | `docs/rfcs/S07.md` | BORRADOR | PLANIFICADO |

**Paralelismo declarado, no inferido:** solo S06 y S07 pueden ejecutarse a la vez, una vez terminado S05. Tocan dominios y vistas distintos y no comparten archivos: S06 trabaja sobre `Documentos` y la búsqueda; S07 sobre agregados de `Digitalizacion` y sobre `Usuarios`. El resto de la secuencia es estrictamente lineal porque cada sprint consume el schema o el estado que introduce el anterior.

**Reparto entre los dos programadores:** ocurre *dentro* de cada sprint, según la tabla de unidades de trabajo de su RFC, no repartiendo sprints distintos en paralelo. Con dos personas y una cadena de dependencias lineal, dividir por unidades dentro del mismo sprint aprovecha mejor el tiempo que forzar sprints simultáneos que se bloquearían entre sí.

## Correspondencia con el cronograma de la propuesta

El cronograma de `docs/propuesta/propuesta.md` está en fases de calendario; este roadmap está en unidades de trabajo. Se corresponden así: la fase 2 (semanas 4–6) cubre S01 y S02; la fase 3 (semanas 7–10) cubre S03 a S05; la fase 4 (semanas 11–12) cubre S06 y S07 en paralelo. La investigación de campo y el benchmark de OCR de la fase 1 no son sprints de software: son insumos que alimentan S04 y la sección de impacto de la propuesta.
