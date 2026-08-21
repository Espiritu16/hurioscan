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
Route::get('/acceder', FormularioAcceso::class)->name('acceder');
Route::post('/acceder', Acceder::class);
Route::post('/salir', Salir::class)->name('salir')->middleware('auth');

Route::get('/pacientes', BuscadorPacientes::class)->name('pacientes');
// `/pacientes/nuevo` va antes que el comodín por legibilidad, pero lo que de
// verdad impide que `{pacienteId}` capture el literal es su `whereNumber`.
Route::get('/pacientes/nuevo', FormularioPaciente::class)->name('pacientes.alta');
Route::get('/pacientes/{pacienteId}', LineaDeTiempo::class)->name('pacientes.detalle')->whereNumber('pacienteId');

Route::get('/sesiones/nueva/{pacienteId}', AperturaSesion::class)->name('sesiones.apertura')->whereNumber('pacienteId');

// Mismo criterio que arriba: el orden ayuda a leerlo, `whereNumber` lo garantiza.
Route::get('/sesiones/pendientes', SesionesPendientes::class)->name('sesiones.pendientes');
Route::get('/sesiones/{sesionId}', CapturaHojas::class)->name('sesiones.detalle')->whereNumber('sesionId');
Route::get('/sesiones/{sesionId}/revision', RevisionOcr::class)->name('sesiones.revision')->whereNumber('sesionId');
Route::get('/sesiones/{sesionId}/cierre', CierreSesion::class)->name('sesiones.cierre')->whereNumber('sesionId');

Route::get('/ilegibles', HojasIlegibles::class)->name('ilegibles');
Route::get('/buscar', BusquedaContenido::class)->name('buscar');
Route::get('/documentos/{documentoId}', VisorDocumento::class)->name('documentos.detalle')->whereNumber('documentoId');

Route::get('/avance', PanelAvance::class)->name('avance');
Route::get('/usuarios', AdministracionUsuarios::class)->name('usuarios');
Route::get('/auditoria', ConsultaAuditoria::class)->name('auditoria');

// Catálogo de componentes: solo en desarrollo, nunca en un entorno servido.
if (app()->environment('local')) {
    Route::view('/componentes', 'componentes.catalogo')->name('componentes');
}
