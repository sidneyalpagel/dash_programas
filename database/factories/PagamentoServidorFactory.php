<?php

namespace Database\Factories;

use App\Models\PagamentoServidor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagamentoServidor>
 */
class PagamentoServidorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exercicio' => 2025,
            'verba' => fake()->unique()->words(3, true),
            'descricao' => fake()->sentence(),
            'qtd_servidores' => fake()->numberBetween(1, 300),
            'valor' => fake()->randomFloat(2, 1000, 5000000),
            'ordem' => 0,
            'publicado' => true,
        ];
    }

    public function rascunho(): static
    {
        return $this->state(fn () => ['publicado' => false]);
    }
}
