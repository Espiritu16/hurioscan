<?php

use App\Dominios\Digitalizacion\Componentes\AperturaSesion;
use App\Dominios\Digitalizacion\Componentes\CapturaHojas;
use App\Dominios\Digitalizacion\Componentes\CierreSesion;
use App\Dominios\Digitalizacion\Componentes\PanelAvance;
use App\Dominios\Digitalizacion\Componentes\SesionesPendientes;
use App\Dominios\Documentos\Componentes\BusquedaContenido;
use App\Dominios\Documentos\Componentes\HojasIlegibles;
use App\Dominios\Documentos\Componentes\RevisionOcr;
use App\Dominios\Documentos\Componentes\VisorDocumento;
use App\Dominios\Pacientes\Componentes\BuscadorPacientes;
use App\Dominios\Pacientes\Componentes\FormularioPaciente;
use App\Dominios\Pacientes\Componentes\LineaDeTiempo;
use App\Dominios\Usuarios\Acciones\Acceder;
use App\Dominios\Usuarios\Acciones\Salir;
use App\Dominios\Usuarios\Componentes\AdministracionUsuarios;
use App\Dominios\Usuarios\Componentes\ConsultaAuditoria;
use App\Dominios\Usuarios\Componentes\FormularioAcceso;
use App\Dominios\Usuarios\DestinoSegunRol;
use Illuminate\Support\Facades\Route;

/*
 * Los nombres de ruta son canónicos: los fija `docs/frontend/integracion.md`
 * y las vistas los consumen por nombre, nunca por su URL literal.
 *
 * El nombre del parámetro (`{sesionId}`, `{pacienteId}`, `{documentoId}`) es
 * el de la propiedad pública del componente Livewire que lo recibe; por eso no
 * se usa `{id}` como en la notación de los contratos, aunque la URL resultante
 * es la misma.
 *
 * Cada ruta declara además qué operación de la matriz de permisos sirve, con
 * `autorizar:<operación>`. Sin esa declaración la ruta no se sirve: es el
 * deny-by-default de RNF-013 aplicado también al montaje, para que agregar una
 * pantalla sin decidir quién puede verla no pase inadvertido.
 *
 * Cada parámetro lleva `whereNumber`. No es cosmético: los componentes tipan
 * esas propiedades como `int`, así que un segmento no numérico reventaba al
 * asignarse y devolvía 500 con su traza, donde el contrato exige 404. Con la
 * restricción, la ruta simplemente no casa y Laravel responde 404.
 */

/*
 * La raíz no es una pantalla: encamina. Sin sesión lleva al formulario de
 * acceso; con sesión, al panel que corresponde al rol. Antes servía la página
 * de bienvenida del scaffold, que no pertenece al producto.
 */
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(DestinoSegunRol::ruta(auth()->user()->rol))
        : redirect()->route('acceder');
});

/*
 * Acceso y salida (RF-011, `docs/contratos/usuarios.md`).
 *
 * `GET /acceder` y `POST /acceder` son las dos operaciones que la matriz de
 * permisos concede al actor `Anónimo`, así que quedan fuera de la autenticación.
 * `POST /acceder` no lleva nombre: el nombre canónico `acceder` es el de la
 * pantalla, y `docs/frontend/integracion.md` fija esa lista.
 */
Route::get('/acceder', FormularioAcceso::class)->name('acceder')->middleware('autorizar:GET /acceder');
Route::post('/acceder', Acceder::class)->middleware('autorizar:POST /acceder');
Route::post('/salir', Salir::class)->name('salir')->middleware('autorizar:POST /salir');

Route::get('/pacientes', BuscadorPacientes::class)->name('pacientes')->middleware('autorizar:GET /pacientes');
// `/pacientes/nuevo` va antes que el comodín por legibilidad, pero lo que de
// verdad impide que `{pacienteId}` capture el literal es su `whereNumber`.
// La pantalla existe para registrar, así que su puerta es la operación de
// registro: el rol `consulta` no puede crear pacientes y tampoco entra aquí.
Route::get('/pacientes/nuevo', FormularioPaciente::class)->name('pacientes.alta')->middleware('autorizar:POST /pacientes');
Route::get('/pacientes/{pacienteId}', LineaDeTiempo::class)->name('pacientes.detalle')->whereNumber('pacienteId')->middleware('autorizar:GET /pacientes/{id}');

Route::get('/sesiones/nueva/{pacienteId}', AperturaSesion::class)->name('sesiones.apertura')->whereNumber('pacienteId')->middleware('autorizar:POST /sesiones');

// Mismo criterio que arriba: el orden ayuda a leerlo, `whereNumber` lo garantiza.
Route::get('/sesiones/pendientes', SesionesPendientes::class)->name('sesiones.pendientes')->middleware('autorizar:GET /sesiones/pendientes');
Route::get('/sesiones/{sesionId}', CapturaHojas::class)->name('sesiones.detalle')->whereNumber('sesionId')->middleware('autorizar:GET /sesiones/{id}');
// La revisión es otra vista de la misma sesión, así que la gobierna la misma
// operación. `GET /sesiones/{id}/hojas`, que el contrato de Documentos declara,
// todavía no tiene fila propia en la matriz; ver el handoff de B01.
Route::get('/sesiones/{sesionId}/revision', RevisionOcr::class)->name('sesiones.revision')->whereNumber('sesionId')->middleware('autorizar:GET /sesiones/{id}');
Route::get('/sesiones/{sesionId}/cierre', CierreSesion::class)->name('sesiones.cierre')->whereNumber('sesionId')->middleware('autorizar:POST /sesiones/{id}/cerrar');

Route::get('/ilegibles', HojasIlegibles::class)->name('ilegibles')->middleware('autorizar:GET /ilegibles');
Route::get('/buscar', BusquedaContenido::class)->name('buscar')->middleware('autorizar:GET /buscar');
Route::get('/documentos/{documentoId}', VisorDocumento::class)->name('documentos.detalle')->whereNumber('documentoId')->middleware('autorizar:GET /documentos/{id}');

Route::get('/avance', PanelAvance::class)->name('avance')->middleware('autorizar:GET /avance');
Route::get('/usuarios', AdministracionUsuarios::class)->name('usuarios')->middleware('autorizar:GET /usuarios');
Route::get('/auditoria', ConsultaAuditoria::class)->name('auditoria')->middleware('autorizar:GET /auditoria');

// Catálogo de componentes: solo en desarrollo, nunca en un entorno servido.
if (app()->environment('local')) {
    Route::view('/componentes', 'componentes.catalogo')->name('componentes');
}
