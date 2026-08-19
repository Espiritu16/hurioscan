{{-- Indicador de paso del flujo de digitalización (F00-UT-04).
     En escritorio muestra la miga «Paciente › Captura › …»; en 360 px,
     «Paso n de m» con barra de progreso por segmentos. --}}
@props(['pasos', 'actual' => 1])

@php $total = count($pasos); @endphp

<nav {{ $attributes }} aria-label="Progreso: paso {{ $actual }} de {{ $total }}">
    <ol class="hidden items-center gap-2 font-mono text-xs tracking-wide uppercase sm:flex">
        @foreach ($pasos as $indice => $paso)
            <li
                @if ($indice + 1 === $actual) aria-current="step" @endif
                @class([
                    'font-semibold text-tinta' => $indice + 1 === $actual,
                    'text-tinta-suave' => $indice + 1 !== $actual,
                ])
            >
                {{ $paso }}
            </li>
            @unless ($loop->last)
                <li aria-hidden="true" class="text-tinta-suave">›</li>
            @endunless
        @endforeach
    </ol>

    <div class="flex items-center gap-3 sm:hidden">
        <span class="shrink-0 font-mono text-xs tracking-wide uppercase">Paso {{ $actual }} de {{ $total }}</span>
        <div class="flex flex-1 gap-1">
            @foreach ($pasos as $indice => $paso)
                <div
                    aria-hidden="true"
                    @class([
                        'h-1 flex-1 rounded-full',
                        'bg-acento' => $indice + 1 <= $actual,
                        'bg-borde' => $indice + 1 > $actual,
                    ])
                ></div>
            @endforeach
        </div>
    </div>
</nav>
