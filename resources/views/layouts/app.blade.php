{{-- Layout de página de Livewire.

     Livewire envuelve aquí todo componente servido como ruta: su valor por
     defecto es `layouts::app`, un espacio de nombres que apunta a este
     directorio. Sin este archivo, cualquier componente montado en una ruta
     falla con «No hint path defined for [layouts]» — y no se nota con
     `Livewire::test()`, que renderiza sin layout.

     No duplica estructura: delega en el `<x-layout>` de F00, que es donde
     viven la cabecera, el menú y las ranuras. --}}
@props(['title' => null])

@php
    // El rol lo aporta la sesión autenticada; mientras B01 no exista, no hay
    // ninguno y el menú no muestra opciones. Es el comportamiento correcto
    // para alguien sin autenticar: ocultar de más, nunca de menos.
    $rol = auth()->user()->rol ?? null;
@endphp

{{-- `<x-layout>` ya sufija « — HuriosCan», así que aquí solo va el título de
     la página; sin él, el layout muestra la marca sola. --}}
<x-layout :titulo="$title ?? 'Archivo clínico'" :rol="$rol">
    {{ $slot }}
</x-layout>
