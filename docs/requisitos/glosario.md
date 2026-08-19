# Glosario — HuriosCan

Términos de negocio con una sola definición. Estado: `propuesto` hasta aprobación del usuario real.

## Acervo documental
- Definición: el conjunto completo de documentos clínicos en papel que el establecimiento conserva archivados antes de digitalizarlos.
- Usado en: RF-010, docs/propuesta/propuesta.md
- Aprobado por: pendiente — fecha: pendiente

## Folder
- Definición: la carpeta física, identificada por un número de historia clínica, donde el establecimiento guarda todas las hojas de un mismo paciente. Es la unidad de trabajo de la digitalización: una sesión digitaliza exactamente un folder.
- Usado en: RF-002, RF-003, RF-006, RF-010, docs/persistencia/modelo.md
- Aprobado por: pendiente — fecha: pendiente

## Sesión de digitalización
- Definición: el trabajo de escanear y revisar todas las hojas de un folder, asociado a un único paciente y a un único operador. Tiene su propio ciclo de vida (ver RF-002).
- Usado en: RF-002, RF-003, RF-006, RF-013, docs/contratos/digitalizacion.md, docs/persistencia/modelo.md
- Aprobado por: pendiente — fecha: pendiente

## Hoja
- Definición: una página física capturada como una imagen. Cada hoja produce un `Documento` en el sistema. "Hoja" y "documento" son la misma cosa vista desde el papel y desde el sistema respectivamente.
- Usado en: RF-003, RF-005, RF-006, RF-014
- Aprobado por: pendiente — fecha: pendiente

## Tipo de documento
- Definición: la clase de documento clínico que representa una hoja. Conjunto cerrado: hoja de atención, receta, resultado de laboratorio, epicrisis, consentimiento informado, otro.
- Usado en: RF-003, RF-008, docs/contratos/digitalizacion.md, docs/persistencia/modelo.md
- Aprobado por: pendiente — fecha: pendiente

## Texto extraído
- Definición: el texto que el motor de OCR produjo a partir de la imagen, antes de cualquier corrección humana. Se conserva aunque el operador lo corrija después, para poder medir la precisión real del motor.
- Usado en: RF-004, RF-005, RNF-012, docs/persistencia/modelo.md
- Aprobado por: pendiente — fecha: pendiente

## Texto corregido
- Definición: el texto que el operador dejó tras revisar la hoja. Es el que se indexa para la búsqueda. Si la hoja se marcó correcta sin editar, coincide con el texto extraído.
- Usado en: RF-005, RF-007, docs/persistencia/modelo.md
- Aprobado por: pendiente — fecha: pendiente

## Hoja ilegible
- Definición: hoja cuya imagen no permite obtener texto confiable. Su imagen se conserva y sigue siendo consultable; simplemente no aporta texto a la búsqueda. No es un error del sistema ni impide cerrar el folder.
- Usado en: RF-005, RF-006, RF-014, docs/errores/manejo-errores.md
- Aprobado por: pendiente — fecha: pendiente

## Motor de OCR
- Definición: el componente intercambiable que convierte la imagen de una hoja en texto. El sistema no depende de una implementación concreta: se elige por configuración entre las disponibles.
- Usado en: RF-004, docs/integraciones/ocr.md, docs/decisiones/0002-motor-ocr-intercambiable.md
- Aprobado por: pendiente — fecha: pendiente

## Número de historia clínica
- Definición: el identificador que el establecimiento asigna a cada paciente y con el que rotula su folder físico. Es una cadena de texto, no un número: puede tener ceros iniciales y separadores. Es único dentro del establecimiento.
- Usado en: RF-001, RF-002, docs/contratos/pacientes.md, docs/persistencia/modelo.md
- Aprobado por: pendiente — fecha: pendiente
