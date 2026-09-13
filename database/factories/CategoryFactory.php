<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'descripcion' => $this->faker->optional(0.7)->paragraph(), // 70% chance of having description
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the category has rich description.
     */
    public function withRichDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'descripcion' => '<p>' . $this->faker->paragraph() . '</p>' .
                           '<p>This description has <strong>formatting</strong> and <em>emphasis</em>.</p>' .
                           '<ul><li>' . $this->faker->sentence() . '</li><li>' . $this->faker->sentence() . '</li></ul>',
        ]);
    }

    /**
     * Indicate that the category has no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'descripcion' => null,
        ]);
    }
}