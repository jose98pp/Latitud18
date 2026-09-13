<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Noticia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Noticia>
 */
class NoticiaFactory extends Factory
{
    protected $model = Noticia::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(6),
            'contenido' => '<p>' . $this->faker->paragraphs(3, true) . '</p>',
            'category_id' => Category::factory(),
            'publicada' => $this->faker->boolean(70), // 70% chance of being published
            'video_youtube' => $this->faker->optional(0.3)->url(), // 30% chance of having video
            'imagen' => $this->faker->optional(0.8)->imageUrl(800, 600, 'news'), // 80% chance of having image
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the noticia is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'publicada' => true,
        ]);
    }

    /**
     * Indicate that the noticia is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'publicada' => false,
        ]);
    }

    /**
     * Indicate that the noticia has rich content.
     */
    public function withRichContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'contenido' => '<h2>' . $this->faker->sentence(4) . '</h2>' .
                          '<p>' . $this->faker->paragraph() . '</p>' .
                          '<p>This paragraph has <strong>bold text</strong> and <em>italic text</em>.</p>' .
                          '<ul><li>' . $this->faker->sentence() . '</li><li>' . $this->faker->sentence() . '</li></ul>' .
                          '<p>' . $this->faker->paragraph() . '</p>',
        ]);
    }

    /**
     * Indicate that the noticia has minimal content.
     */
    public function withMinimalContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'contenido' => '<p>' . $this->faker->sentence() . '</p>',
        ]);
    }
}