# ADR-0001: monolito Laravel + Livewire en un solo repositorio
- Estado: **aprobada**
- Aprobado por (Arquitectura): sesión Coordinación+Arquitectura, con aprobación de Kevin — fecha: 2026-08-19
- Contexto: el equipo tiene dos personas con experiencia real en PHP/Laravel de un total de cinco integrantes, y un ciclo de doce semanas. La alternativa habitual —Laravel como API más un frontend separado en React o Vue— exige mantener un contrato entre ambos, resolver CORS y autenticación por token, y coordinar dos despliegues.
- Decisión: un solo proyecto Laravel que sirve también la interfaz, con Blade y Livewire para las partes interactivas (grilla de captura, revisión de texto). Un solo repositorio, un solo despliegue.
- Consecuencias: se pierde la posibilidad de que otro cliente consuma una API pública sin trabajo adicional, y el equipo no practica la separación cliente/servidor. A cambio, no hay contrato entre repositorios que mantener y las doce semanas se gastan en funcionalidad. Si más adelante hiciera falta una API pública, los servicios de dominio ya están separados de las vistas y se expondrían sin reescribir la lógica.
