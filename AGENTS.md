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
- Persistencia/motor: PostgreSQL 18.3 (verificado instalado: `psql --version`; conexión de la app se configura en B01)
- Estado global / roadmap: `docs/roadmap.md` y `docs/estado.md`
- Handoffs de sprint: `docs/handoffs/<sprint-id>.md` — existen los de D01 y los siete de la cadena de frontend

## Vigencia de gobernanza
- Estado de gobernanza: APROBADO
- **Última revisión material (2026-08-20):** reparto de `tests/Feature/` por línea con zona de corrección cruzada, y declaración de que el proyecto no usa `app/Models/`. Motivo: el archivo afirmaba que las rutas de las dos líneas eran disjuntas y `tests/Feature/` se solapaba entre ambas — una separación declarada que no existía, en la que dos ejecutores en paralelo habrían confiado. Detectado al auditar B01.
- Revisión material anterior: separación del rol `implementation` en dos líneas paralelas (backend y frontend) con rutas escribibles disjuntas, para permitir ejecución simultánea. Aprobada por Kevin el 2026-08-18.
- Revisión no material (2026-08-20): se actualizó la descripción del mecanismo de despacho —subagente por defecto en vez de chat de rol— sin tocar roles, permisos, rutas, ramas ni autoridad. No invalidó la aprobación.
- Aprobado por: Kevin
- Fecha de aprobación: 2026-08-20

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

El rol se ejerce en **dos líneas que pueden trabajar en paralelo** — sprints `B` (backend) y sprints `F` (frontend). Sus rutas escribibles son disjuntas a propósito: es lo que permite ejecutar dos sprints a la vez sin que se pisen, sea quien sea el ejecutor.

#### implementation · línea backend (sprints `B`)
- Puede escribir código y pruebas: `app/Dominios/*/` **excepto** la subcarpeta `Componentes/` de cada dominio, `app/Compartido/`, `app/Http/Middleware/`, `app/Providers/`, `tests/Feature/Backend/`, `tests/Unit/`
- Puede escribir bootstrap/configuración cuando el RFC lo autoriza: `composer.json`, `composer.lock`, `config/`, `bootstrap/`, `.env.example` (sin secretos), `database/migrations/`, `database/seeders/`, `database/factories/`
- **Es dueña de `routes/web.php`**: declara las rutas con su nombre. La línea frontend las consume por nombre y no las edita.
- No puede escribir: `resources/views/`, `resources/css/`, `resources/js/`, `app/Dominios/*/Componentes/`

#### implementation · línea frontend (sprints `F`)
- Puede escribir código y pruebas: `app/Dominios/*/Componentes/` (componentes Livewire), `resources/views/`, `resources/css/`, `resources/js/`, `app/Compartido/Dobles/` (dobles de desarrollo), `tests/Feature/Frontend/`
- Puede escribir bootstrap/configuración cuando el RFC lo autoriza: `package.json`, `pnpm-lock.yaml`, `vite.config.js`, `tailwind.config.js`
- **Consume las rutas por su nombre**, nunca por su URL literal ni editando `routes/web.php`. Si necesita una ruta que no existe, la pide al Coordinador; no la agrega por su cuenta.
- No puede escribir: `app/Dominios/*/` fuera de `Componentes/`, `database/`, `app/Compartido/` fuera de `Dobles/`, `tests/Feature/Backend/`, `tests/Unit/`

#### Reglas comunes a ambas líneas
- Puede actualizar únicamente este handoff: `docs/handoffs/<sprint-id>.md` correspondiente a su propio sprint; nunca `docs/estado.md` ni handoffs ajenos
- No puede modificar sin autorización: `docs/contratos/`, `docs/persistencia/modelo.md`, `docs/errores/`, `docs/requisitos/actores-permisos.md`, `docs/decisiones/`, `docs/roadmap.md`
- **Zona compartida — `routes/web.php`:** la escribe solo backend. Si un sprint de frontend necesita una ruta antes de que su backend exista, el Coordinador la declara apuntando a un componente provisional; nunca dos ejecutores editan ese archivo a la vez.
- **Los dobles de desarrollo viven en `app/Compartido/Dobles/` y se activan solo por configuración.** Ningún doble se referencia desde código de producción, y el punto de integración de cada par `B`/`F` verifica que el build real no cae de vuelta a ellos.

##### Pruebas — reparto por línea y corrección cruzada

- La línea **backend** escribe `tests/Feature/Backend/` y `tests/Unit/`; la línea **frontend** escribe `tests/Feature/Frontend/`.
- **Ninguna escribe en la raíz de `tests/Feature/`**: toda prueba nueva va dentro de la subcarpeta de su línea. La raíz queda reservada al scaffold del framework.
- **Corrección cruzada — zona compartida, mismo criterio que `routes/web.php`.** Cuando un sprint rompe legítimamente una prueba de la otra línea, porque su cambio altera lo que esa prueba daba por cierto, **puede ajustarla**, y debe declararlo en su handoff para que QA lo verifique por mutación.

  **Ajustar una expectativa no es relajarla, y la diferencia importa:**

  | Permitido — ajustar al comportamiento correcto nuevo | Prohibido — debilitar la prueba |
  |---|---|
  | Cambiar `assertOk()` por `assertRedirect()` porque esa ruta ahora exige credencial | **Quitar** la aserción |
  | Añadir el rol o la sesión que el comportamiento nuevo requiere | **Ampliarla** para que acepte más casos de los que aceptaba |
  | Actualizar el valor esperado cuando el correcto cambió | Marcarla **omitida** o condicionarla para que no corra |

  La regla de fondo: después del ajuste la prueba debe **seguir pudiendo fallar por lo mismo que fallaba antes**. Si la única forma de hacerla pasar es que deje de vigilar algo, eso no es un ajuste: es un bloqueo, y se escala en vez de editar la prueba ajena.

##### `app/Models/` — el proyecto no la usa

Las entidades viven en el dominio que les corresponde (ADR-0001, estructura domain-first). `app/Models/` es residuo del scaffold de Laravel. **Ninguna línea escribe ahí**, y el directorio con su `User.php` se elimina en el primer sprint de backend que toque esa capa —ya viaja dentro de `sprint/B01`, junto con el ajuste de `config/auth.php`, el seeder y la factory que aún lo referencian—. Si alguna línea necesitara escribir ahí, es señal de que la entidad está en el lugar equivocado, no de que falte un permiso.

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

## Cómo se despacha el trabajo

> Actualizado el 2026-08-20. Describe el mecanismo, no la autoridad: roles, permisos, rutas, ramas y gates son los mismos de antes.

- **Por defecto, un sprint habilitado se ejecuta con un subagente** que el Coordinador despacha. El subagente lee este `AGENTS.md`, el RFC del sprint y su handoff, trabaja en su propio worktree y **cierra declarando un outcome de una lista cerrada** (`terminado` / `parcial` / `bloqueado` para implementación y devops; `aprobado` / `rechazado` / `bloqueado` para QA). Un turno que termina en prosa sin outcome no está terminado.
- **Un subagente no tiene canal con el usuario.** No puede consultarle nada a mitad del trabajo ni aprobar lo que la gobernanza exige de él —un RFC, un waiver, este archivo—. Ante cualquiera de esos casos **cierra con `bloqueado`** diciendo qué falta y quién debe resolverlo; el Coordinador lo recibe y lo escala. Por eso un sprint con decisiones abiertas sin cerrar es mal candidato para despacho automático: se bloqueará enseguida.
- **El chat de rol abierto por el usuario queda para lo que exige conversación**: un sprint cuyas decisiones se resuelven hablando, desbloquear lo que un subagente devolvió `bloqueado`, o cuando el usuario quiere seguir el razonamiento en vivo. El Coordinador no abre chats; genera el prompt y el usuario lo pega.
- **Lo que no cambia:** el aislamiento por worktree y las rutas escribibles disjuntas de la sección siguiente rigen igual, sea subagente o chat quien ejecute. Dos ejecutores simultáneos sobre el mismo árbol siguen siendo el riesgo que esas dos capas existen para evitar.
- **Todo reporte se verifica contra Git antes de darlo por bueno**, venga de un subagente o de un chat: que el `final_sha` exista y sea el head real de su rama, que no se haya escrito fuera del área autorizada, y que la evidencia sea salida de comandos y no una afirmación.

## Política de ramas
- protegida: `main` — solo recibe integraciones desde `develop`; representa lo que se muestra y se entrega
- integración: `develop` — donde se acumula el trabajo de los sprints antes de llegar a `main`
- trabajo: `sprint/<id>`, `feature/<nombre>`, `fix/<nombre>`, `docs/<nombre>` — siempre nacen de `develop` y vuelven a `develop`
- Entrega de Implementación: PR desde la rama de trabajo hacia `develop`
- Integración a `main`: PR desde `develop`, decidido por el Coordinador al cerrar un sprint o antes de una entrega del curso; nunca un commit directo
- Gate antes de integrar a `develop`: QA APROBADO sobre ese `final_sha` cuando el sprint requiere QA
- Gate antes de integrar a `main`: la suite completa en verde sobre `develop` — `main` nunca recibe algo que no haya pasado por `develop` primero

## Aislamiento del trabajo paralelo

Este repositorio es **fullstack**: backend y frontend viven en el mismo árbol. Dos sprints ejecutándose a la vez necesitan por eso dos capas de protección, y ninguna sustituye a la otra.

**Capa 1 — worktree por sprint.** Cada sprint que se ejecuta en paralelo con otro usa su propio worktree de Git en la rama `sprint/<id>`, creado desde el mismo `base_sha`. Un ejecutor por worktree. Ninguno toca el checkout principal ni el worktree de otro sprint. La ruta queda registrada en `worktree_path` del handoff, y **cuando el worktree se retira, ese campo se marca como retirado** indicando la rama que conserva el contenido — un `worktree_path` que apunta a un directorio inexistente manda a trabajar a la nada.

```bash
git worktree add ../hurioscan-F00 -b sprint/F00 develop
git worktree add ../hurioscan-B01 -b sprint/B01 develop
```

Esto aísla el sistema de archivos: dos agentes editando a la vez no se sobrescriben.

**Capa 2 — rutas escribibles disjuntas.** El worktree evita que se pisen mientras escriben, pero **no evita el conflicto al fusionar**: dos ramas que editan el mismo archivo chocan igual en el merge. Por eso las dos líneas de `implementation` tienen rutas declaradas y separadas más arriba, y `routes/web.php` tiene un dueño único.

**Antes de despachar dos sprints en paralelo**, el Coordinador verifica que no compartan rutas escribibles según esa separación. Si al materializar el trabajo aparece un archivo compartido que la separación no previó, la línea base resultó incorrecta: se pausa, se corrige `AGENTS.md` y se vuelve a aprobar, en vez de resolver conflictos de merge a mano cada vez.

**Por qué no se separó en dos repositorios.** Sería la otra forma de conseguir el aislamiento —cada repositorio ya es un árbol de trabajo propio— pero obligaría a Laravel API más un frontend independiente, lo que reabre `docs/decisiones/0001-monolito-laravel-livewire.md` y agrega un contrato entre repositorios que mantener. Con dos programadores y doce semanas, worktrees más rutas disjuntas dan el mismo aislamiento a un costo mucho menor.

## CI por rama
- `develop`: `.github/workflows/verificacion-develop.yml` — GitHub Actions ejecutando `./vendor/bin/pint --test`, `php artisan test` y `pnpm build` en cada PR
- `main`: `.github/workflows/verificacion-main.yml` — los checks anteriores más la suite de Feature tests sobre un servicio PostgreSQL 18 con migraciones desde base limpia, en cada PR desde `develop`

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
- Prohibido: commit directo a `main` y a `develop`; todo pasa por PR
- Prohibido: `--force` push a `main` o `develop` sin autorización explícita

## Comandos de verificación
- Lint: `./vendor/bin/pint --test` — ejecutado 2026-08-18, resultado `passed`
- Tests unitarios/componentes: `php artisan test` — ejecutado 2026-08-18, 2 tests, 2 passed
- Integración/contrato: `php artisan test --testsuite=Feature` con base PostgreSQL de prueba — ejecutado en CI por `verificacion-main.yml`, verde el 2026-08-19
- E2E: no aplica — el proyecto no incorpora navegador automatizado en el horizonte planificado; la verificación de flujo se cubre con tests Feature de Livewire
- Accesibilidad: revisión de teclado, foco y contraste sobre las vistas de consulta — ejecutada en F06 el 2026-08-19 (29 elementos evaluados para contraste componiendo fondos translúcidos, 0 incumplimientos; 8 enfocables, todos con foco visible y nombre accesible). Evidencia en `docs/handoffs/F06.md`
- Build: `pnpm build` — ejecutado 2026-08-18, `built in 530ms`, artefactos en `public/build/`
- Otros RNF: previsto en B06 — medición del tiempo de búsqueda de texto completo contra el umbral de RNF-001

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
- Pipeline: GitHub Actions — `.github/workflows/verificacion-develop.yml` y `verificacion-main.yml`, entregados en D01
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
- Interfaces de servicio y selección de dobles: `docs/contratos/servicios-aplicacion.md`
- Frontend — experiencia/integración: `docs/frontend/`
- Persistencia: `docs/persistencia/`
- Manejo de errores: `docs/errores/`
- Arquitectura (visión general, opcional): no aplica — proyecto de un solo repositorio; la visión general vive en `docs/propuesta/propuesta.md`
- Integraciones externas: `docs/integraciones/`
- ADR: `docs/decisiones/`
- RFCs por sprint: `docs/rfcs/`
