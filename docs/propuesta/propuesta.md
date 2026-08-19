# HuriosCan — Sistema de digitalización del acervo documental clínico

**Propuesta de proyecto · Curso de Innovación Digital**


| | |
|---|---|
| **Integrantes** | *[POR COMPLETAR — los 5 nombres]* |
| **Curso** | Innovación Digital |
| **Establecimiento de referencia** | *[POR COMPLETAR — nombre de la posta, centro de salud u hospital]* |
| **Fecha** | *[POR COMPLETAR]* |

---

## 1. Resumen ejecutivo

Los establecimientos de salud del MINSA conservan su historia clínica en papel, archivada en folders físicos organizados por número de historia clínica. Ese acervo crece cada año, ocupa espacio, se deteriora, se traspapela y —sobre todo— **solo se puede consultar yendo físicamente al archivo y buscando a mano.**

**HuriosCan** digitaliza ese acervo y lo convierte en un archivo consultable. El personal de archivo toma cada folder físico, lo escanea completo con un celular, y el sistema aplica reconocimiento óptico de caracteres (OCR) para extraer el texto de cada hoja. A partir de ese momento, cualquier documento de cualquier paciente se encuentra en segundos, y se puede buscar **por el contenido del documento**, no solo por el nombre del paciente.

La innovación no está en escanear papeles: está en que el archivo pasa de ser un depósito a ser **consultable**. Buscar "metformina" y obtener todos los documentos donde aparece esa palabra es algo que un archivo físico no puede hacer, sin importar cuán bien esté ordenado.

---

## 2. El problema

### 2.1 Contexto

En los establecimientos de salud del primer nivel de atención, la historia clínica sigue siendo predominantemente física. Cada paciente tiene un folder identificado por su número de historia clínica, y dentro se acumulan hojas de atención, recetas, resultados de laboratorio, consentimientos informados y epicrisis — la mayoría llenadas a mano.

### 2.2 Magnitud

*[POR COMPLETAR — DATO DE CAMPO. Esta es la sección más importante del documento y no se puede inventar. Consíganla visitando o entrevistando a personal del establecimiento:]*

- Cantidad aproximada de folders en el archivo físico: **[dato]**
- Tiempo promedio en localizar un documento específico: **[dato]**
- Documentos que se solicitan por día: **[dato]**
- Personas dedicadas al archivo: **[dato]**
- Documentos perdidos o deteriorados al año, si hay registro: **[dato]**

> **Nota para el equipo:** una sola cifra real y verificable vale más que una página de generalidades. Si consiguen "hoy toma X minutos encontrar un documento", ese número sostiene toda la propuesta y toda la sustentación.

### 2.3 Consecuencias

- **Tiempo perdido en cada consulta.** El personal clínico espera, o atiende sin el antecedente a la vista.
- **Deterioro irreversible.** El papel se humedece, se rompe, se decolora. Lo que se pierde no se recupera.
- **Traspapeleo.** Una hoja archivada en el folder equivocado es, en la práctica, una hoja perdida.
- **Imposibilidad de análisis.** No se puede responder "¿cuántos pacientes con diagnóstico X atendimos este año?" sin revisar folder por folder.
- **Dependencia de la presencia física.** El archivo solo sirve a quien está parado frente a él.

---

## 3. La propuesta

### 3.1 Qué es

Un sistema web que permite:

1. **Digitalizar** el acervo físico existente, folder por folder, usando un celular como escáner.
2. **Extraer automáticamente el texto** de cada documento mediante OCR.
3. **Consultar** cualquier documento de cualquier paciente en segundos, con búsqueda por contenido.

### 3.2 Qué NO es

Delimitar el alcance es parte de la propuesta:

- **No reemplaza el documento original.** La imagen escaneada se conserva siempre y es la fuente válida. El texto extraído es únicamente un índice de búsqueda.
- **No es un sistema de historia clínica electrónica.** No registra atenciones nuevas ni sustituye al RENHICE. Digitaliza lo que ya existe en papel.
- **No promete precisión perfecta.** El OCR sobre escritura a mano tiene límites reconocidos, y el diseño los asume explícitamente (ver sección 5.1 y 7).

### 3.3 Propuesta de valor

| Antes | Después |
|---|---|
| Buscar un documento exige ir al archivo | Se consulta desde cualquier computadora del establecimiento |
| Solo se busca por paciente | Se busca por paciente **y por contenido del documento** |
| El deterioro es pérdida definitiva | Existe una copia digital permanente |
| Un documento a la vez, una persona a la vez | Varias personas consultan el mismo documento simultáneamente |
| No se pueden hacer estadísticas | Base para análisis futuro del acervo |

---

## 4. Cómo funciona

El sistema tiene **dos flujos distintos**, con usuarios, frecuencias y objetivos diferentes. Distinguirlos es la decisión de diseño central del proyecto.

### 4.1 Actores

| Actor | Qué hace | Cuándo |
|---|---|---|
| Operador de digitalización | Escanea los folders del archivo físico | Durante la campaña de digitalización |
| Personal de admisión y clínico | Busca y consulta documentos | Diariamente, de forma permanente |
| Administrador | Gestiona usuarios, mide avance, audita accesos | Permanente |

El esfuerzo de carga es **finito**; el beneficio de consulta es **permanente**. Esa asimetría es la justificación económica del proyecto.

### 4.2 Flujo A — Carga (la campaña de digitalización)

**Paso 1 · Abrir sesión de lote.** El operador toma un folder físico del archivador y lo abre en el sistema por su número de historia clínica.

**Paso 2 · Identificar al paciente.** Si ya existe en el sistema, se selecciona. Si no existe —lo habitual al inicio, porque todo estaba en papel— se crea la ficha con los datos mínimos: número de historia clínica, DNI, nombres y fecha de nacimiento. El paciente queda fijado como encabezado de toda la sesión.

**Paso 3 · Capturar las hojas.** Una foto por hoja, en orden, con el celular o un escáner. No hace falta indicar de quién es cada hoja: lo determina el lote.

**Paso 4 · Etiquetar cada hoja.** Tipo de documento (receta, laboratorio, hoja de atención, epicrisis, consentimiento) y fecha, heredando por defecto los valores de la hoja anterior para agilizar la carga.

**Paso 5 · Preprocesar automáticamente.** Recorte de bordes, enderezado, ajuste de contraste y conversión a escala de grises. Es invisible para el operador y determinante para el resultado del OCR.

**Paso 6 · Guardar el original de inmediato.** La imagen se almacena antes de cualquier procesamiento. El OCR se ejecuta en segundo plano: el operador nunca espera por él para continuar escaneando.

**Paso 7 · Revisar el texto extraído.** Pantalla dividida: imagen a la izquierda, texto reconocido a la derecha. El operador corrige lo esencial y marca cada hoja como *correcta*, *corregida* o *ilegible*.

**Paso 8 · Cerrar el lote.** El sistema muestra un resumen (por ejemplo, 27 hojas, 24 correctas, 3 ilegibles). Al confirmar, el folder físico se rotula como digitalizado para no repetir el trabajo.

**Por qué el lote y no documento por documento:** en un archivo físico los documentos ya están agrupados por paciente dentro de un folder. Seleccionar el paciente una vez por folder, en lugar de una vez por hoja, convierte la identificación de una fricción constante en un encabezado único de sesión.

### 4.3 Flujo B — Consulta (el uso diario, permanente)

**Paso 1 · Buscar al paciente** por DNI, nombre o número de historia clínica.

**Paso 2 · Ver su línea de tiempo.** Todos sus documentos ordenados por fecha, con miniatura y tipo.

**Paso 3 · Filtrar** por tipo de documento o rango de fechas.

**Paso 4 · Buscar dentro del texto.** El usuario escribe un término —un medicamento, un examen, un diagnóstico— y el sistema muestra en qué documentos aparece, con la coincidencia resaltada. **Esta es la capacidad que el archivo físico no puede ofrecer de ninguna manera, y es el núcleo del argumento de innovación.**

**Paso 5 · Abrir un documento.** Imagen original a pantalla completa, texto extraído al costado, opciones de descarga e impresión.

---

## 5. Decisiones técnicas

### 5.1 Estrategia de OCR

**Decisión: no se desarrollará ni entrenará un motor de OCR propio.** Se integrará uno existente, elegido mediante evaluación comparativa.

**Fundamento de descartar el desarrollo propio:**

- Los conjuntos de datos públicos de escritura a mano disponibles (IAM en inglés, RIMES en francés, Esposalles y Rodrigo en español pero de documentos históricos de los siglos XVI y XVII) no se corresponden con documentos clínicos peruanos contemporáneos. Entrenar con ellos produciría un modelo desalineado con el caso de uso.
- Construir un conjunto de datos adecuado exigiría etiquetar manualmente miles de imágenes, consumiendo el ciclo completo del proyecto.
- El resultado sería inferior a las alternativas existentes de acceso libre.

**Evaluación comparativa (a ejecutar en las semanas 2–3):**

Se reunirá un conjunto de 25 a 30 imágenes de documentos clínicos (impresos y manuscritos) y se procesarán con distintos motores, midiendo la precisión de reconocimiento de cada uno:

| Motor | Tipo | Característica |
|---|---|---|
| Tesseract | Clásico, local | Requiere poco hardware; precisión reducida en manuscrito |
| PaddleOCR | Neuronal, local | El motor de código abierto más completo actualmente |
| docTR | Neuronal, local | Orientado a formularios y documentos administrativos |
| Modelo de visión-lenguaje (VLM) | Por API | Interpreta la región completa y comprende terminología médica |

**Referencia del estado del arte:** sobre texto médico manuscrito, los motores de OCR tradicionales alcanzan aproximadamente **50–70 %** de precisión, mientras que los modelos de visión-lenguaje alcanzan **82–95 %**. La diferencia se explica porque un modelo de visión-lenguaje no reconoce caracteres aislados: interpreta el contexto clínico y puede inferir un término parcialmente ilegible. Ninguna herramienta disponible alcanza el 99 % en escritura a mano, razón por la cual el paso de revisión humana del flujo de carga es un requisito del diseño y no un añadido opcional.

El motor ganador de la evaluación se integrará detrás de una interfaz propia, de modo que pueda sustituirse sin modificar el resto del sistema.

### 5.2 Arquitectura y tecnologías

| Componente | Tecnología | Justificación |
|---|---|---|
| Aplicación | **Laravel 13** (PHP 8.3+) | Última versión estable (marzo 2026); es el stack que el equipo domina |
| Base de datos | **PostgreSQL** | Incorpora búsqueda de texto completo nativa; no requiere un motor de búsqueda adicional |
| Almacenamiento de imágenes | Sistema de archivos, con la ruta registrada en la base de datos | Las imágenes no se almacenan dentro de la base de datos |
| Procesamiento OCR | Cola de trabajos en segundo plano | El operador no queda bloqueado esperando el reconocimiento |
| Organización del código | Por dominio de negocio | Cada dominio (pacientes, documentos, digitalización) agrupa su modelo, controlador y servicios |

### 5.3 Modelo de datos

**Paciente** — número de historia clínica, DNI, nombres, fecha de nacimiento.

**Documento** — paciente al que pertenece, tipo, fecha del documento, ruta de la imagen original, texto extraído, estado de revisión, operador que lo digitalizó, fecha de digitalización.

**Sesión de digitalización** — paciente, operador, fecha, cantidad de hojas, estado. Permite trazabilidad y medición del avance de la campaña.

---

## 6. Alcance del proyecto

### 6.1 Incluido

- Gestión de pacientes (registro y búsqueda)
- Flujo de carga completo: sesión de lote, captura, preprocesamiento, OCR, revisión y cierre
- Flujo de consulta completo: búsqueda de paciente, línea de tiempo, búsqueda por contenido, visor de documento
- Panel de avance de la campaña de digitalización
- Control de acceso por rol y registro de auditoría
- Evaluación comparativa de motores de OCR documentada

### 6.2 Excluido

- Registro de atenciones nuevas (no es una historia clínica electrónica)
- Integración con RENHICE u otros sistemas del MINSA
- Extracción de campos estructurados (diagnóstico, medicamentos, dosis) — ver 6.3
- Aplicación móvil nativa (la captura se hace desde el navegador del celular)

### 6.3 Fase 2 (fuera del alcance del curso, dentro de la visión)

Una vez consolidado el archivo digital, el texto ya extraído habilita una segunda etapa: **identificar campos estructurados** dentro de los documentos —diagnósticos codificados, medicamentos, resultados de laboratorio— y convertirlos en datos analizables. Eso permitiría responder preguntas epidemiológicas sobre el acervo histórico del establecimiento.

Se presenta como visión de escalamiento, no como compromiso de este proyecto.

---

## 7. Riesgos y mitigaciones

| Riesgo | Impacto | Mitigación |
|---|---|---|
| **Baja precisión del OCR en escritura a mano** | Alto | El texto es un índice de búsqueda, nunca reemplaza al original. Paso de revisión humana obligatorio en el flujo de carga. Elección del motor basada en evaluación medida, no en supuestos. |
| **Volumen del acervo** | Alto | No se digitaliza todo simultáneamente. Se prioriza por criterio clínico: pacientes con atención en los últimos dos años primero. |
| **Baja adopción del personal** | Alto | El escáner es el celular que el personal ya tiene. La capacitación es de una sesión. El flujo de consulta es más rápido que ir al archivo, lo que genera adopción por conveniencia. |
| **Calidad variable de las imágenes** | Medio | Preprocesamiento automático y validación en el momento de la captura, antes de cerrar el lote. |
| **Documento asignado al paciente equivocado** | Alto | El lote fija el paciente una sola vez, eliminando el error por hoja. El cierre de lote exige confirmación explícita. |
| **Manejo de datos personales sensibles** | Alto | Ver sección 8. |

---

## 8. Marco normativo y protección de datos

*(Durante el desarrollo del proyecto no se utilizará información real de pacientes; se trabajará con documentos de muestra. Esta sección describe las condiciones requeridas para una implementación real.)*

- **Ley N° 29733, Ley de Protección de Datos Personales.** Los datos de salud son datos sensibles y requieren un tratamiento con mayores garantías.
- **Ley N° 30024**, que crea el Registro Nacional de Historias Clínicas Electrónicas (RENHICE). HuriosCan no sustituye ni compite con RENHICE: digitaliza el acervo físico preexistente.

**Medidas contempladas en el diseño:**

- Acceso restringido por rol: el operador de digitalización no requiere acceso al historial clínico completo.
- Registro de auditoría: queda constancia de quién consultó qué documento y cuándo.
- Los originales digitalizados se conservan íntegros, sin modificación.
- El servidor se aloja dentro de la red del establecimiento; las imágenes no salen de su infraestructura.
- Si el motor de OCR elegido es un servicio externo, esa decisión debe evaluarse explícitamente frente al requisito anterior — es un punto de decisión, no un detalle de implementación.

---

## 9. Impacto y métricas

### 9.1 Indicadores de éxito

| Indicador | Medición | Meta |
|---|---|---|
| Tiempo de localización de un documento | Antes vs. después | *[POR COMPLETAR con el dato de campo]* |
| Porcentaje del acervo digitalizado | Folders digitalizados / total | Definido por la campaña |
| Precisión del OCR | Evaluación comparativa | Establecida por la medición de las semanas 2–3 |
| Hojas digitalizadas por hora | Medición durante la prueba | Referencia operativa de la campaña |
| Consultas realizadas por semana | Registro del sistema | Indicador de adopción real |

### 9.2 Impacto esperado

- **Para el personal clínico:** disponer del antecedente del paciente durante la atención, no después.
- **Para el archivo:** preservación permanente de documentos en deterioro.
- **Para el establecimiento:** base para análisis del acervo histórico que hoy es inaccesible.

---

## 10. Plan de trabajo

### 10.1 Roles

| Rol | Responsabilidad | Entregable |
|---|---|---|
| Desarrollo backend | Base de datos, integración de OCR, lógica de negocio | Sistema funcional |
| Desarrollo frontend | Interfaz de las siete pantallas | Interfaz operativa |
| Investigación de campo | Datos reales del problema | Cifras verificables del establecimiento |
| Propuesta y documentación | Documento del proyecto | Esta propuesta y su versión final |
| Diseño y sustentación | Prototipo visual y presentación | Prototipo y exposición final |

### 10.2 Cronograma (12 semanas)

**Fase 1 · Semanas 1–3 — Entender y decidir**
- Investigación de campo en el establecimiento
- Evaluación comparativa de motores de OCR
- Primera versión de la propuesta

**Fase 2 · Semanas 4–6 — Diseñar**
- Prototipo visual de las siete pantallas
- Modelo de datos y estructura del proyecto Laravel
- Propuesta corregida con los resultados de la evaluación

**Fase 3 · Semanas 7–10 — Construir**
- Flujo de carga completo
- Flujo de consulta completo
- Integración del motor de OCR seleccionado

**Fase 4 · Semanas 11–12 — Cerrar**
- Pruebas con documentos de muestra y medición de resultados
- Propuesta final y ensayo de sustentación

**Nota sobre el orden:** la evaluación comparativa de OCR se ejecuta en la semana 2, no al final. Su resultado determina qué motor se integra en la fase 3 y aporta la cifra que fundamenta el paso de revisión humana en todo el diseño.

### 10.3 Pantallas del prototipo

1. Panel de avance de la campaña
2. Nueva sesión de digitalización (búsqueda o creación de paciente)
3. Captura del lote
4. Revisión del OCR (imagen y texto en paralelo)
5. Cierre de lote
6. Búsqueda de paciente y línea de tiempo de documentos
7. Visor de documento individual

---

## 11. Viabilidad

**Costos de implementación:**

| Concepto | Costo |
|---|---|
| Software (Laravel, PostgreSQL, motor de OCR de código abierto) | Sin costo de licencia |
| Escáner | El celular del operador, sin inversión adicional |
| Servidor | Un equipo del establecimiento o un servidor de bajo costo |
| Motor de OCR por API, en caso de elegirse | *[POR COMPLETAR según el resultado de la evaluación]* |
| Capacitación | Una sesión por operador |

La barrera principal del proyecto no es económica ni tecnológica: es **el tiempo de personal dedicado a la campaña de digitalización**. Dimensionarlo con precisión requiere el dato de hojas digitalizadas por hora, que se obtiene durante la fase de pruebas.

---

## 12. Pendientes del equipo

- [ ] Datos de campo del establecimiento (sección 2.2)
- [ ] Nombre definitivo del sistema
- [ ] Ejecución de la evaluación comparativa de OCR (sección 5.1)
- [ ] Confirmación del establecimiento de referencia
- [ ] Revisión de la rúbrica del curso para ajustar el peso de cada sección
