# Diseño de interfaz

## `hurioscan-claude-design.html`

Diseño de las pantallas generado con Claude Design a partir del prompt derivado de `docs/frontend/experiencia.md` y de la paleta definida en `docs/decisiones/`. Es la **referencia visual** para implementar las vistas Blade y Livewire.

**Necesita conexión a internet para abrirse**: el archivo es un bundle que carga React desde `unpkg.com`. Abierto sin red muestra la pantalla de carga y no avanza.

**No es código fuente del proyecto.** No se importa ni se copia dentro de `resources/views/`: las vistas se escriben en Blade siguiendo este diseño, no incrustando su HTML.

**Autoridad:** la fuente canónica del comportamiento sigue siendo `docs/frontend/experiencia.md` — estados visibles, navegación y criterios verificables. Si el diseño y ese documento se contradicen, manda el documento y el diseño se corrige, no al revés.

## `../../propuesta/prototipo.html`

Prototipo navegable anterior de las 7 pantallas, con la vista de celular y de escritorio. Se conserva porque el flujo completo está conectado de punta a punta y sirve para la sustentación.
