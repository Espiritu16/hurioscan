// Informa al componente cuántos archivos eligió la persona, antes de subirlos.
//
// Es el único punto donde ese número existe: si el servidor descarta archivos
// —por ejemplo al superar `max_file_uploads` de PHP— nunca llegan, así que
// contarlos allá no revelaría la pérdida. El componente compara este número
// con lo que resolvió y avisa si falta algo.
document.addEventListener('change', (evento) => {
    const entrada = evento.target.closest('input[type="file"][data-cuenta-seleccion]');
    if (!entrada) return;

    const raiz = entrada.closest('[wire\\:id]');
    if (!raiz || !window.Livewire) return;

    const componente = window.Livewire.find(raiz.getAttribute('wire:id'));
    if (!componente) return;

    componente.set('seleccionadas', entrada.files.length);
});

// Alterna el menú lateral en anchos pequeños (F00-UT-04).
document.addEventListener('click', (evento) => {
    const boton = evento.target.closest('[data-alterna-menu]');
    if (!boton) return;

    const menu = document.getElementById('menu-lateral');
    if (!menu) return;

    menu.classList.toggle('hidden');
    boton.setAttribute('aria-expanded', String(!menu.classList.contains('hidden')));
});
