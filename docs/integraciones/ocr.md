# Integración — Motor de OCR

Estado: `propuesto` hasta aprobación.

## Qué resuelve
Convierte la imagen de cada hoja escaneada en texto, que es lo que hace consultable el acervo (RF-004, RF-007). El sistema no depende de una implementación concreta: la elige por configuración.

## Dirección
El proyecto **consume** el motor. Ninguno de ellos consume nada de HuriosCan.

## Motores contemplados

| Motor | `OCR_MOTOR` | Naturaleza | Dónde vive la credencial | Estado |
|---|---|---|---|---|
| Nulo | `nulo` | interna — devuelve texto vacío | no aplica | disponible; es el default de desarrollo y tests |
| Tesseract | `tesseract` | binario local, ejecutado como proceso | no aplica — no requiere credencial | propuesto |
| PaddleOCR | `paddleocr` | servicio HTTP local en contenedor | no aplica — servicio local sin autenticación, accesible solo desde la red interna | propuesto |
| Visión-lenguaje | `vlm` | API externa de terceros | `OCR_VLM_API_KEY` en `.env` — nunca versionada | propuesto |

**Cuál se usa en producción es una decisión pendiente del benchmark descrito en `docs/propuesta/propuesta.md`.** La arquitectura está diseñada para que esa decisión no bloquee el desarrollo: se implementa contra la interfaz y se cambia el valor de `OCR_MOTOR`.

## Contrato interno que todos cumplen

Interfaz `App\Compartido\Ocr\MotorOcr` — es la frontera que aísla al resto del sistema de cualquier motor concreto:

```
extraerTexto(string $rutaImagen): ResultadoOcr
```

`ResultadoOcr` expone: `texto` (string, puede ser vacío), `motor` (string, identificador del motor que lo produjo) y `confianza` (float 0–1, o null si el motor no la reporta).

Un motor **nunca** lanza una excepción hacia el dominio por un fallo suyo: la traduce a `OCR_NO_DISPONIBLE`, y el documento queda en `PENDIENTE_OCR` para reintentar (RF-004).

## Contrato del tercero
- Tesseract: documentación oficial del proyecto Tesseract OCR.
- PaddleOCR: documentación oficial de PaddleOCR.
- Motor de visión-lenguaje: la documentación del proveedor elegido; su versión exacta se fija al decidirlo tras el benchmark.

No se copia aquí la documentación de ninguno.

## Manejo de fallas

| Situación | Comportamiento |
|---|---|
| El binario o servicio no responde | `OCR_NO_DISPONIBLE`; el documento queda `PENDIENTE_OCR`; se registra en `LogError` con severidad `error` |
| El motor responde pero devuelve texto vacío | Resultado válido, no un fallo. El documento pasa a `EN_REVISION` y el operador lo marcará `ILEGIBLE` si corresponde |
| El motor externo agota su cuota o rechaza la credencial | `OCR_NO_DISPONIBLE`; **el mensaje registrado nunca incluye la credencial** (RNF-014) |
| El motor tarda más de 120 segundos | Se cancela y se trata como no disponible; el job se reintenta hasta 3 veces con espera creciente, y después queda para reintento manual |

**La captura nunca espera al OCR** (RNF-002): el procesamiento vive en la cola, y un motor caído no impide seguir escaneando el folder.

## Dato que nunca sale del establecimiento

Si se elige un motor externo por API (`vlm`), **las imágenes de documentos clínicos se envían a un tercero**. Con datos de muestra del proyecto académico no hay riesgo. Para una implementación real esa decisión debe evaluarse explícitamente frente a la Ley N.º 29733 y a la medida de `AGENTS.md` que exige que las imágenes no salgan de la infraestructura del establecimiento: son incompatibles, y elegir el motor externo obliga a revisar esa medida, no a ignorarla. Queda registrado como decisión abierta, no como detalle de implementación.

Aprobado por: pendiente — fecha: pendiente
