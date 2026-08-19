# Experiencia frontend — HuriosCan

La UI la sirve el propio backend con Blade + Livewire; no hay SPA ni cliente HTTP separado.
Estado: `propuesto` hasta aprobación del usuario real.

Referencia visual: `docs/frontend/diseno/hurioscan-claude-design.html` (diseño de pantallas) y `docs/propuesta/prototipo.html` (prototipo navegable del flujo completo). Ver `docs/frontend/diseno/README.md` para qué autoridad tiene cada uno.

| Flujo | Ruta UI | Actor | Deriva de | Entrada/disparador | Estados visibles | Navegación esperada | Criterio verificable |
|---|---|---|---|---|---|---|---|
| Acceder al sistema | `/acceder` | Anónimo | RF-011 | URL directa, o redirección desde cualquier ruta protegida | idle, enviando, error de credenciales | tras acceder redirige al panel del rol; un actor ya autenticado que entra a `/acceder` va directo a su panel | credenciales inválidas muestran un mensaje único sin revelar si el correo existe; el foco vuelve al primer campo con error |
| Ver avance de la campaña | `/avance` | operador, administrador | RF-010 | enlace del menú lateral o URL directa | carga, sin datos configurados, éxito | es la vista inicial tras acceder para ambos roles | con `totalFoldersAcervo` sin configurar muestra el avance absoluto y ninguna barra de porcentaje, no un 0 % engañoso |
| Buscar o registrar paciente | `/pacientes` | operador, consulta, administrador | RF-001 | menú lateral, o botón "Nueva sesión" | idle, buscando, vacío, error, éxito | al seleccionar un paciente el operador continúa a la captura; los demás roles llegan a su línea de tiempo | buscar un valor inexistente muestra un estado vacío con la acción de registrar, no un error; el rol `consulta` no ve el formulario de registro |
| Abrir sesión de digitalización | `/sesiones` (acción) | operador | RF-002 | botón "Iniciar digitalización" desde el paciente seleccionado | enviando, error de sesión ya abierta, éxito | redirige a la captura de la sesión creada | si el paciente ya tiene una sesión sin cerrar, se ofrece **retomarla** con un enlace directo, en vez de dejar el error sin salida |
| Capturar hojas del folder | `/sesiones/{id}` | operador | RF-003 | continuación de la apertura, o retomar desde pendientes | idle, subiendo, error por hoja, éxito por hoja | permanece en la misma vista mientras se capturan hojas; el indicador de paso muestra Paciente › Captura › Revisión › Cierre | las tres vías de captura (cámara, galería, archivos) están disponibles en 360 px; una hoja rechazada por formato o tamaño muestra el motivo sin perder las ya capturadas |
| Revisar el texto extraído | `/sesiones/{id}/revision` | operador | RF-005 | botón "Continuar a revisión" | esperando OCR, en revisión, guardando, conflicto de versión, éxito | avanza hoja por hoja sin salir de la vista; al terminar habilita el cierre | una hoja todavía en `PENDIENTE_OCR` muestra que el OCR sigue corriendo, no un panel vacío; un conflicto de versión muestra el texto vigente y deja que la persona decida, sin reenviar en silencio |
| Cerrar el folder | `/sesiones/{id}/cierre` | operador | RF-006 | botón "Terminar revisión" | resumen, confirmando, error por hojas sin revisar, éxito | tras cerrar vuelve al panel de avance con el folder contabilizado | el resumen muestra hojas por estado y por tipo antes de confirmar; una hoja ilegible aparece en el resumen y no bloquea el cierre |
| Retomar sesión pendiente | `/sesiones/pendientes` | operador, administrador | RF-013 | menú lateral | carga, vacío, éxito | al retomar entra a captura o revisión según el estado de la sesión | la sesión se recupera con todas sus hojas ya capturadas y en su orden |
| Buscar por contenido | `/buscar` | operador, consulta, administrador | RF-007 | menú lateral o buscador de la cabecera | idle, buscando, vacío, error, éxito | los resultados enlazan al documento; volver atrás conserva el término y la página | el término aparece resaltado dentro del fragmento; un término sin coincidencias muestra estado vacío, no error |
| Ver línea de tiempo del paciente | `/pacientes/{id}` | operador, consulta, administrador | RF-008 | desde la búsqueda de pacientes o desde un resultado | carga, vacío, éxito | los filtros de tipo y fecha se reflejan en la URL, de modo que refrescar o compartir el enlace conserva la vista | cambiar un filtro actualiza lista y conteo en la misma acción, sin un segundo clic |
| Ver un documento | `/documentos/{id}` | operador, consulta, administrador | RF-009 | desde la línea de tiempo o desde un resultado de búsqueda | carga, error, éxito | volver atrás regresa al origen conservando su estado | un documento `ILEGIBLE` muestra la imagen y dice explícitamente que no tiene texto asociado |
| Revisar hojas ilegibles | `/ilegibles` | operador, administrador | RF-014 | menú lateral | carga, vacío, éxito | reabrir una hoja lleva a su revisión | tras reabrir, la hoja desaparece de la lista |
| Administrar usuarios | `/usuarios` | administrador | RF-011 | menú lateral (solo visible para administrador) | carga, vacío, error, éxito | permanece en la vista tras crear o editar | el intento de quitarse el propio rol de administrador se rechaza con un mensaje que explica por qué |
| Consultar auditoría | `/auditoria` | administrador | RF-012 | menú lateral (solo visible para administrador) | carga, vacío, éxito | los filtros se reflejan en la URL | los valores mostrados son solo los campos de la allowlist; ningún texto de documento ni contraseña aparece |

## Reglas transversales de la interfaz

- **La barra del paciente es fija durante toda la sesión de digitalización** (captura, revisión y cierre) y muestra nombre y número de historia clínica con la marca "fijado para esta sesión". Es la garantía visual de RF-002.
- **El menú lateral muestra solo lo que el rol puede hacer.** Ocultar una opción es una ayuda de interfaz, no un control de seguridad: el backend vuelve a validar siempre (RNF-013).
- **Estados vacíos con salida**: toda vista sin datos ofrece la acción siguiente, nunca un espacio en blanco.
- **Responsive** en 360, 768, 1024 y 1440 px, según RNF-004. Captura y revisión son operables con el pulgar en 360 px.
- **El texto extraído por OCR se muestra siempre escapado**, tanto en el visor como en el fragmento resaltado de resultados (RNF-012).

Estado: propuesto
Aprobado por: pendiente — fecha: pendiente
