# HuriosCan

Digitaliza el acervo documental clínico en papel de un establecimiento de salud: se escanea cada folder físico, un OCR extrae el texto de las hojas y el archivo queda consultable por paciente y por el contenido de los documentos.

## Stack
- PHP 8.5 · Laravel 13 · Livewire 4
- PostgreSQL 18 (búsqueda de texto completo nativa)
- Tailwind CSS 4 · Vite
- Gestor de paquetes: Composer (PHP) y pnpm (assets)

## Cómo correrlo
```bash
cp .env.example .env   # completar los valores requeridos — .env nunca se commitea
composer install
pnpm install
php artisan key:generate
php artisan migrate
php artisan queue:work &   # el OCR corre en segundo plano
composer dev
```

## Comandos
- Build: `pnpm build`
- Tests: `php artisan test`
- Lint: `./vendor/bin/pint`
- Migraciones: `php artisan migrate`

## Estructura
Organización domain-first: cada dominio de negocio vive completo en una sola carpeta bajo `app/Dominios/`, con su modelo, controlador, servicio y form requests juntos. Ver la skill `laravel-estructura`.

```
app/Dominios/{Pacientes,Documentos,Digitalizacion,Usuarios}/
app/Compartido/Ocr/     # MotorOcr (interfaz) + implementaciones intercambiables
```

## Documentación técnica
- Requisitos: `docs/requisitos/`
- Operaciones y validaciones: `docs/contratos/`; experiencia e integración de UI: `docs/frontend/`
- Persistencia: `docs/persistencia/`
- Decisiones de arquitectura: `docs/decisiones/`
- Propuesta del proyecto: `docs/propuesta/propuesta.md`

## Convenciones y gobernanza
Ver `AGENTS.md`.
