<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();

        User::all()->each(function ($user) use ($books) {
            $favoriteBookIds = $books
                ->random(rand(3, 5))
                ->pluck('id')
                ->toArray();

            $user->favoriteBooks()->syncWithoutDetaching($favoriteBookIds);
        });
    }
}
