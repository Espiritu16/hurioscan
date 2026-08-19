// Alterna el menú lateral en anchos pequeños (F00-UT-04).
document.addEventListener('click', (evento) => {
    const boton = evento.target.closest('[data-alterna-menu]');
    if (!boton) return;

    const menu = document.getElementById('menu-lateral');
    if (!menu) return;

    menu.classList.toggle('hidden');
    boton.setAttribute('aria-expanded', String(!menu.classList.contains('hidden')));
});
