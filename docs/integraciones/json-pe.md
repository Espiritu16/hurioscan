# Integración — JSON.pe (consulta de DNI ante RENIEC)

Estado: `propuesto` hasta aprobación.

## Qué resuelve
Trae nombres y apellidos a partir del DNI al registrar un paciente (RF-015), para que el operador no los tipee y no se introduzcan errores de escritura en el archivo clínico.

## Dirección
El proyecto **consume** el servicio. JSON.pe no consume nada de HuriosCan.

## Por qué este proveedor y no RENIEC directamente

RENIEC **no expone una API pública para desarrolladores**. Su servicio oficial de verificación de identidad cuesta S/ 0.40 por consulta y requiere convenio institucional, que un proyecto académico no puede tramitar en un ciclo. JSON.pe actúa como intermediario autorizado y ofrece un plan gratuito.

Queda registrado para la implementación real: **si el establecimiento adopta el sistema, corresponde el convenio directo con RENIEC**, no un intermediario comercial. Consultar la identidad de un paciente es tratamiento de datos personales bajo la Ley N.º 29733, y hacerlo a través de un tercero comercial agrega un encargado de tratamiento que ese convenio evita. No es un detalle de infraestructura: es una decisión que debe tomarse explícitamente antes de usar el sistema con pacientes reales.

Evidencia de que esto importa: otro proveedor conocido del mercado peruano retiró su consulta pública de DNI precisamente por la normativa de protección de datos, y hoy solo la ofrece bajo convenio privado para fines específicos.

## Contrato del tercero

- Documentación oficial: `https://docs.json.pe/api-consulta/endpoint/dni` — no se copia aquí.
- Endpoint: `POST https://api.json.pe/api/dni`
- Autenticación: header `Authorization: Bearer <token>`
- Content-Type: `application/json`
- Cuerpo de la petición: `{ "dni": "27427864" }`

Respuesta exitosa (campos con sus nombres literales del proveedor):

```json
{
  "success": true,
  "message": "exito",
  "data": {
    "numero": "27427864",
    "nombre_completo": "APELLIDO1 APELLIDO2, NOMBRES",
    "nombres": "NOMBRES",
    "apellido_paterno": "APELLIDO1",
    "apellido_materno": "APELLIDO2",
    "codigo_verificacion": 7,
    "direccion": "",
    "direccion_completa": "",
    "ubigeo_reniec": "",
    "ubigeo_sunat": "",
    "ubigeo": [null, null, null]
  }
}
```

**Solo se consumen cuatro campos**: `nombres`, `apellido_paterno`, `apellido_materno` y `numero`. Los demás se ignoran deliberadamente:

- `direccion` y los campos de ubigeo **llegan vacíos** en la respuesta del plan de consulta; no se promete un dato que el proveedor no entrega.
- La dirección del paciente **no forma parte del alcance** de HuriosCan: el sistema digitaliza documentos clínicos, no mantiene un padrón de contacto. Traer y almacenar un dato personal que ningún requisito necesita sería recolección innecesaria, justo lo que la Ley N.º 29733 pide evitar.
- `codigo_verificacion` es un dígito de control del DNI, sin uso en este sistema.

## Mapeo al modelo propio

| Campo del proveedor | Campo de `Paciente` |
|---|---|
| `data.apellido_paterno` + `data.apellido_materno` | `apellidos` (concatenados con un espacio) |
| `data.nombres` | `nombres` |
| `data.numero` | se contrasta con el DNI consultado; si difieren, se descarta la respuesta |

El resultado **nunca se guarda directamente**: se precarga en el formulario, el operador lo revisa y puede corregirlo, y recién al confirmar se persiste (RF-015).

## Dónde vive la credencial

Variable `JSONPE_TOKEN` en `.env`, nunca versionada. El token se obtiene registrándose en `app.json.pe`.

## Créditos y vigencia

- 1 crédito por consulta.
- El plan gratuito da **100 créditos con vigencia de 30 días**.

**Consecuencia práctica para el cronograma:** el proyecto dura 12 semanas y la ventana gratuita es de 30 días. Registrarse al inicio deja los créditos vencidos para la sustentación. Lo previsto es desarrollar y probar contra el proveedor simulado, y activar la cuenta real cerca de la demostración final. Está anotado como criterio del sprint S02, no como algo que alguien deba recordar.

## Manejo de fallas

| Situación | Comportamiento del sistema |
|---|---|
| El DNI no existe en RENIEC | `IDENTIDAD_NO_ENCONTRADA`. El formulario queda editable para carga manual. No es un error del sistema. |
| El proveedor no responde o supera 5 segundos | `IDENTIDAD_PROVEEDOR_NO_DISPONIBLE`. Carga manual disponible. Se registra en `LogError` sin incluir el token. |
| Token inválido o ausente | `IDENTIDAD_PROVEEDOR_NO_DISPONIBLE` para el operador — no se le expone un problema de configuración que no puede resolver. En `LogError` sí se distingue el motivo real, con el token enmascarado. |
| Créditos agotados o vencidos | Igual que el caso anterior: el operador ve que la consulta no está disponible y registra a mano. |
| La respuesta llega con un `numero` distinto al DNI consultado | Se descarta la respuesta y se trata como no encontrada. Nunca se precargan datos de otra persona. |

**Ninguna de estas situaciones bloquea el registro del paciente** (RF-015). La consulta es una ayuda de carga, no un requisito.

## Sobre las pruebas

Los DNI ficticios no existen en RENIEC: contra el proveedor real siempre devolverán `IDENTIDAD_NO_ENCONTRADA`. Las pruebas automatizadas corren contra el proveedor simulado (`IDENTIDAD_PROVEEDOR=simulado`), con una tabla fija de DNIs de ejemplo. La verificación contra el proveedor real se hace de forma manual y puntual, y queda registrada en el handoff del sprint.

Aprobado por: pendiente — fecha: pendiente
