{{-- Encabezado de columna, ordenable cuando recibe una URL (F00-UT-03).
     La lógica de orden vive en la vista que consume; aquí solo la señal
     visual y el aria-sort. --}}
@props(['orden' => null, 'url' => null])

<th
    scope="col"
    @if ($orden) aria-sort="{{ $orden === 'asc' ? 'ascending' : 'descending' }}" @endif
    {{ $attributes->class(['px-3 py-2 font-mono text-xs font-medium tracking-wide text-tinta-suave uppercase']) }}
>
    @if ($url)
        <a
            href="{{ $url }}"
            class="inline-flex items-center gap-1 rounded hover:text-tinta focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-acento"
        >
            {{ $slot }}
            <span aria-hidden="true">@if ($orden === 'asc')▲@elseif ($orden === 'desc')▼@else↕@endif</span>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
