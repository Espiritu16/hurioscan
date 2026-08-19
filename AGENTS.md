# AGENTS.md — hurioscan

> Define permisos, ramas y convenciones locales que project-continuity debe
> respetar. No redefine el proceso general de trabajo (vive en la skill);
> solo declara lo específico de este repositorio.

## Proyecto
- Proyecto: HuriosCan — digitalización del acervo documental clínico
- Identificador canónico del repositorio: `hurioscan`
- Tipo de repositorio: fullstack (Laravel entrega la UI con Blade + Livewire; no expone API pública)
- Stack/framework: Laravel 13.26.1 + Livewire 4.4.1 (verificado: `composer show`)
- Runtime y versión: PHP 8.5.9 (verificado: `php -v`); Node 24.19.0 para el build de assets (verificado: `node -v`)
- Gestor de paquetes/build: Composer 2.10.2 para PHP; pnpm 11.22.0 + Vite 8.2.1 para assets (verificado)
- Persistencia/motor: PostgreSQL 18.3 (verificado instalado: `psql --version`; conexión de la app se configura en S01)
- Estado global / roadmap: `docs/roadmap.md` y `docs/estado.md`
- Handoffs de sprint: `docs/handoffs/<sprint-id>.md` (previsto: se crea el primero en S01)

## Vigencia de gobernanza
- Estado de gobernanza: BORRADOR
- Aprobado por: pendiente
- Fecha de aprobación: pendiente

<!-- Un archivo nuevo permanece BORRADOR aunque exista o esté commiteado.
     Cambiar tipo/stack, roles, permisos, ramas, gates, push o autoridad invalida la
     aprobación. Completar un valor `previsto` dentro del alcance exacto ya
     aprobado no la invalida. -->

## Roles activos en este repositorio

### coordinacion
- Puede escribir gobernanza y planificación: `AGENTS.md`, `docs/roadmap.md`, `docs/estado.md`, `docs/rfcs/`, y las secciones de resultados QA/DevOps de `docs/handoffs/`
- Puede redactar borradores documentales dentro de: `docs/requisitos/`, `docs/contratos/`, `docs/persistencia/`, `docs/errores/`, `docs/frontend/`, `docs/integraciones/`, `docs/decisiones/`, `docs/propuesta/`
- No puede: implementar código, autoaprobar al usuario, sustituir la aprobación de Arquitectura, emitir el veredicto QA ni ejecutar trabajo DevOps

### implementation
- Puede escribir código y pruebas: `app/Dominios/`, `app/Compartido/`, `app/Http/Middleware/`, `app/Providers/`, `routes/`, `resources/views/`, `resources/css/`, `resources/js/`, `tests/`
- Puede escribir bootstrap/configuración cuando el RFC lo autoriza: `composer.json`, `composer.lock`, `package.json`, `pnpm-lock.yaml`, `config/`, `bootstrap/`, `vite.config.js`, `.env.example` (sin secretos), `database/migrations/`, `database/seeders/`, `database/factories/`
- Puede actualizar únicamente este handoff: `docs/handoffs/<sprint-id>.md` correspondiente a su propio sprint; nunca `docs/estado.md` ni handoffs ajenos
- No puede modificar sin autorización: `docs/contratos/`, `docs/persistencia/modelo.md`, `docs/errores/`, `docs/requisitos/actores-permisos.md`, `docs/decisiones/`

### qa
- Puede leer: todo el repositorio
- Debe validar con la skill `qa-validacion`, usando el RFC y los `final_sha` exactos
- Debe evaluar y activar automáticamente `seguridad-validacion` según superficie/riesgo; no requiere un rol `security` separado por defecto
- Puede ejecutar: los comandos de verificación declarados abajo y crear artefactos desechables fuera del árbol objetivo
- No puede escribir ni commitear durante la validación. Automatización o fixtures versionados requieren un sprint separado bajo rol `implementation`; QA puede definir los casos
- No puede: hacer merge, modificar implementación para pasar pruebas

### arquitectura
- Única autoridad sobre: contratos/operaciones, schema de persistencia, ADR, interfaces (incluida `MotorOcr`), manejo de errores (formato y taxonomía), actores/permisos sobre cada operación, e integración frontend técnica
- Puede escribir/proponer dentro de: `docs/contratos/`, `docs/persistencia/`, `docs/errores/`, `docs/requisitos/actores-permisos.md`, `docs/integraciones/`, `docs/frontend/integracion.md`, `docs/decisiones/`
- No puede: implementar código por ejercer Arquitectura, autoaprobar decisiones reservadas al usuario, sustituir QA ni cerrar sprints

### devops
- Debe usar la skill `devops-entrega` y reportar sobre `hurioscan@final_sha` + artefacto/digest exactos
- Puede escribir: `.github/workflows/`, `Dockerfile`, `compose.yaml`, scripts de build/deploy
- Ejecución delegada al agente `devops-engineer` cuando esté disponible
- Puede operar sin nueva aprobación solo: entorno local y CI de GitHub Actions sobre este repositorio
- No puede: desplegar a producción, alterar infraestructura externa real, rotar secretos, ni ejecutar operaciones destructivas sin autorización explícita del usuario

## Política de ramas
- protegida: `main`
- integración: ninguna, se integra directo a la protegida vía PR
- trabajo: `sprint/<id>`, `feature/<nombre>`, `fix/<nombre>`
- Entrega de Implementación: PR desde la rama de trabajo hacia `main`
- Gate antes de integrar: QA APROBADO sobre ese `final_sha` cuando el sprint requiere QA; el Coordinador integra y cierra

## CI por rama
- `main`: previsto en S01 — GitHub Actions ejecutando `./vendor/bin/pint --test`, `php artisan test` y `pnpm build` en cada PR

## Convención de commits
<!-- Este bloque debe ser autocontenible. No citar exclusivamente una ruta
     personal de un solo agente. -->
- Formato: `<tipo>(<alcance>): <descripción corta>`
  tipos válidos: feat, fix, chore, docs, test, refactor, style, perf
- El prefijo va en inglés (estándar); la descripción y el cuerpo van en español,
  en estilo impersonal en pasado ("se agregó", "se corrigió"), nunca imperativo
- Referenciar el sprint en el cuerpo: `Sprint: <id-sprint>`
- Un commit = un cambio lógico coherente
- Sin emojis, sin línea `Co-Authored-By`
- Prohibido: commit directo a `main`; todo pasa por PR
- Prohibido: `--force` push a `main` sin autorización explícita

## Comandos de verificación
- Lint: `./vendor/bin/pint --test` — ejecutado 2026-08-18, resultado `passed`
- Tests unitarios/componentes: `php artisan test` — ejecutado 2026-08-18, 2 tests, 2 passed
- Integración/contrato: previsto en S01 — `php artisan test --testsuite=Feature` con base PostgreSQL de prueba
- E2E: no aplica — el proyecto no incorpora navegador automatizado en el horizonte planificado; la verificación de flujo se cubre con tests Feature de Livewire
- Accesibilidad: previsto en S06 — revisión manual de teclado, foco y contraste sobre las vistas de consulta
- Build: `pnpm build` — ejecutado 2026-08-18, `built in 530ms`, artefactos en `public/build/`
- Otros RNF: previsto en S06 — medición del tiempo de búsqueda de texto completo contra el umbral de RNF-001

## Validación QA
- Entorno autorizado: local, sobre PostgreSQL 18.3 con base descartable creada por migraciones
- Identidades/datos de prueba: seeders de `database/seeders/` con pacientes y documentos ficticios; nunca datos reales de pacientes
- Evidencia durable: `docs/handoffs/<sprint-id>.md` bajo responsabilidad del Coordinador, más los logs de CI cuando exista
- Retención de artefactos externos: logs de GitHub Actions, retención por defecto de la plataforma
- Navegadores/viewports requeridos: 360, 768, 1024 y 1440 px sobre las vistas de consulta y captura
- Seguridad de aplicación: automática por superficie/riesgo mediante `seguridad-validacion`
- Motores permitidos: auto según plataforma; ninguno se asume disponible
- Alcance dinámico autorizado: local únicamente
- Gate especializado: RNF-010 a RNF-014 (bloque de seguridad); un reporte de motor no sustituye el veredicto QA

## Operación DevOps/Release
- Proveedor/topología: no decidido — el proyecto es académico y no tiene despliegue productivo comprometido
- Ambientes: local y CI de GitHub Actions; no hay staging ni producción
- Pipeline: previsto en S01 — GitHub Actions
- Artefacto canónico: no aplica — no hay despliegue en el horizonte planificado
- Configuración/secretos: `.env` local a partir de `.env.example`; ningún secreto versionado
- Migraciones: `php artisan migrate`; responsable Implementación dentro del sprint que las introduce
- Health/smoke: no aplica — sin ambiente desplegado
- Observabilidad: `LogError` en base más logs estructurados a `stderr` vía el canal de Laravel; proporcional a un proyecto sin producción
- Rollback/roll-forward: no aplica — sin ambiente desplegado
- Evidencia durable: logs de GitHub Actions cuando exista el pipeline
- Retención: por defecto de la plataforma
- Producción: requiere autorización explícita del usuario inmediatamente antes de mutar

## Documentación de referencia
- Requisitos (RF/RNF): `docs/requisitos/`
- Glosario: `docs/requisitos/glosario.md`
- Actores y permisos: `docs/requisitos/actores-permisos.md`
- Contratos/API: `docs/contratos/` — operaciones de aplicación (rutas web y acciones Livewire); el proyecto no expone API HTTP pública
- Frontend — experiencia/integración: `docs/frontend/`
- Persistencia: `docs/persistencia/`
- Manejo de errores: `docs/errores/`
- Arquitectura (visión general, opcional): no aplica — proyecto de un solo repositorio; la visión general vive en `docs/propuesta/propuesta.md`
- Integraciones externas: `docs/integraciones/`
- ADR: `docs/decisiones/`
- RFCs por sprint: `docs/rfcs/`
