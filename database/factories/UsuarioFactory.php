<?php

namespace Database\Factories;

use App\Dominios\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /** La contraseña se hashea una sola vez por corrida: hashear cuesta. */
    protected static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('hurioscan'),
            'rol' => 'operador',
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function conRol(string $rol): static
    {
        return $this->state(fn () => ['rol' => $rol]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }
}
