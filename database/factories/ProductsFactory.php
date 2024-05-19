<?php

namespace Database\Factories;

use App\Models\CategoriesModel;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Ambil satu kategori dan satu pengguna secara acak
        $category = CategoriesModel::inRandomOrder()->first();
        $user = \App\Models\User::inRandomOrder()->first(); // Menggunakan absolute namespace karena terjadi konflik dengan alias User

        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->text(100),
            'price' => $this->faker->randomNumber(5, true),
            'image' => $this->faker->imageUrl(),
            'category_id' => $category->id,
            'expired_at' => $this->faker->date(),
            'modified_by' => $user->id, // Menggunakan ID pengguna
        ];
    }
}

