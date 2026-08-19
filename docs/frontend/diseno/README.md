# Diseño de interfaz

## `hurioscan-claude-design.html`

Diseño de las pantallas generado con Claude Design a partir del prompt derivado de `docs/frontend/experiencia.md`. Es la **referencia visual** para implementar las vistas Blade y Livewire.

**Paleta canónica:** las variables CSS de este mismo archivo (ningún ADR de `docs/decisiones/` contiene colores). Es la fuente de los tokens de F00. Solo el **tema claro** está en el horizonte del proyecto; el tema oscuro que el archivo también define queda fuera, por decisión de Kevin del 2026-08-18 (ver `docs/estado.md`).

**Necesita conexión a internet para abrirse**: el archivo es un bundle que carga React desde `unpkg.com`. Abierto sin red muestra la pantalla de carga y no avanza.

**No es código fuente del proyecto.** No se importa ni se copia dentro de `resources/views/`: las vistas se escriben en Blade siguiendo este diseño, no incrustando su HTML.

**Autoridad:** la fuente canónica del comportamiento sigue siendo `docs/frontend/experiencia.md` — estados visibles, navegación y criterios verificables. Si el diseño y ese documento se contradicen, manda el documento y el diseño se corrige, no al revés.

## `../../propuesta/prototipo.html`

Prototipo navegable anterior de las 7 pantallas, con la vista de celular y de escritorio. Se conserva porque el flujo completo está conectado de punta a punta y sirve para la sustentación.
