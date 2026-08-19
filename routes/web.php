<?php

use App\Dominios\Digitalizacion\Componentes\CapturaHojas;
use App\Dominios\Digitalizacion\Componentes\CierreSesion;
use App\Dominios\Digitalizacion\Componentes\PanelAvance;
use App\Dominios\Digitalizacion\Componentes\SesionesPendientes;
use App\Dominios\Documentos\Componentes\BusquedaContenido;
use App\Dominios\Documentos\Componentes\HojasIlegibles;
use App\Dominios\Documentos\Componentes\RevisionOcr;
use App\Dominios\Documentos\Componentes\VisorDocumento;
use App\Dominios\Pacientes\Componentes\BuscadorPacientes;
use App\Dominios\Pacientes\Componentes\LineaDeTiempo;
use App\Dominios\Usuarios\Componentes\AdministracionUsuarios;
use App\Dominios\Usuarios\Componentes\ConsultaAuditoria;
use App\Dominios\Usuarios\Componentes\FormularioAcceso;
use Illuminate\Support\Facades\Route;

/*
 * Los nombres de ruta son canónicos: los fija `docs/frontend/integracion.md`
 * y las vistas los consumen por nombre, nunca por su URL literal.
 *
 * El nombre del parámetro (`{sesionId}`, `{pacienteId}`, `{documentoId}`) es
 * el de la propiedad pública del componente Livewire que lo recibe; por eso no
 * se usa `{id}` como en la notación de los contratos, aunque la URL resultante
 * es la misma.
 */

Route::get('/', function () {
    return view('welcome');
});

Route::get('/acceder', FormularioAcceso::class)->name('acceder');

Route::get('/pacientes', BuscadorPacientes::class)->name('pacientes');
Route::get('/pacientes/{pacienteId}', LineaDeTiempo::class)->name('pacientes.detalle');

// `/sesiones/pendientes` va antes que `/sesiones/{sesionId}`: al revés, el
// parámetro capturaría el literal «pendientes».
Route::get('/sesiones/pendientes', SesionesPendientes::class)->name('sesiones.pendientes');
Route::get('/sesiones/{sesionId}', CapturaHojas::class)->name('sesiones.detalle');
Route::get('/sesiones/{sesionId}/revision', RevisionOcr::class)->name('sesiones.revision');
Route::get('/sesiones/{sesionId}/cierre', CierreSesion::class)->name('sesiones.cierre');

Route::get('/ilegibles', HojasIlegibles::class)->name('ilegibles');
Route::get('/buscar', BusquedaContenido::class)->name('buscar');
Route::get('/documentos/{documentoId}', VisorDocumento::class)->name('documentos.detalle');

Route::get('/avance', PanelAvance::class)->name('avance');
Route::get('/usuarios', AdministracionUsuarios::class)->name('usuarios');
Route::get('/auditoria', ConsultaAuditoria::class)->name('auditoria');

// Catálogo de componentes: solo en desarrollo, nunca en un entorno servido.
if (app()->environment('local')) {
    Route::view('/componentes', 'componentes.catalogo')->name('componentes');
}

/*
 * Pendiente de la línea backend: `salir` (`POST /salir`, RF-011). No se monta
 * aquí porque no tiene componente que la sirva — es una acción de sesión que
 * implementa B01. El menú la muestra deshabilitada mientras no exista.
 */
