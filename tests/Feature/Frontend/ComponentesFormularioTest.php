<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;

class ComponentesFormularioTest extends TestCase
{
    public function test_el_boton_renderiza_sus_tres_variantes(): void
    {
        $primario = $this->blade('<x-boton>Guardar</x-boton>');
        $primario->assertSee('bg-acento');
        $primario->assertSee('type="button"', false);

        $secundario = $this->blade('<x-boton variante="secundario">Cancelar</x-boton>');
        $secundario->assertSee('border-borde');

        $terciario = $this->blade('<x-boton variante="terciario">Ver más</x-boton>');
        $terciario->assertSee('text-acento');
    }

    public function test_el_boton_acepta_atributos_propios(): void
    {
        $enviar = $this->blade('<x-boton type="submit" disabled>Enviar</x-boton>');
        $enviar->assertSee('type="submit"', false);
        $enviar->assertSee('disabled');
    }

    public function test_el_campo_asocia_la_etiqueta_con_el_control(): void
    {
        $campo = $this->blade('<x-campo nombre="dni" etiqueta="DNI" />');
        $campo->assertSee('for="dni"', false);
        $campo->assertSee('id="dni"', false);
        $campo->assertSee('name="dni"', false);
    }

    public function test_el_campo_anuncia_el_error_de_forma_accesible(): void
    {
        $campo = $this->blade('<x-campo nombre="dni" etiqueta="DNI" error="El DNI debe tener 8 dígitos." />');
        $campo->assertSee('role="alert"', false);
        $campo->assertSee('aria-invalid="true"', false);
        $campo->assertSee('aria-describedby="dni-error"', false);
        $campo->assertSee('id="dni-error"', false);
        $campo->assertSee('El DNI debe tener 8 dígitos.');
    }

    public function test_el_campo_sin_error_no_marca_aria_invalid(): void
    {
        $campo = $this->blade('<x-campo nombre="dni" etiqueta="DNI" />');
        $campo->assertDontSee('aria-invalid', false);
        $campo->assertDontSee('role="alert"', false);
    }

    public function test_el_buscador_tiene_etiqueta_accesible(): void
    {
        $buscador = $this->blade('<x-buscador etiqueta="Buscar en el archivo" />');
        $buscador->assertSee('sr-only');
        $buscador->assertSee('Buscar en el archivo');
        $buscador->assertSee('type="search"', false);
    }
}
