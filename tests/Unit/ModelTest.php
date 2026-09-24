<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    private function createBook(User $user): Book
    {
        return Book::create([
            'user_id' => $user->id,
            'title' => 'モデルテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234580001',
            'published_date' => '2026-09-21',
            'description' => 'モデルテスト用です。',
            'image_url' => null,
        ]);
    }

    public function test_user_has_many_books(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user);

        $this->assertTrue(
            $user->books->contains($book)
        );
    }

    public function test_book_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user);

        $this->assertTrue(
            $book->user->is($user)
        );
    }

    public function test_book_belongs_to_many_genres(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user);

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $book->genres()->attach($genre->id);

        $this->assertTrue(
            $book->genres->contains($genre)
        );
    }

    public function test_genre_belongs_to_many_books(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user);

        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book->genres()->attach($genre->id);

        $this->assertTrue(
            $genre->books->contains($book)
        );
    }

    public function test_book_has_many_reviews(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();

        $book = $this->createBook($owner);

        $review = Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'モデルテストレビュー',
        ]);

        $this->assertTrue(
            $book->reviews->contains($review)
        );
    }

    public function test_review_belongs_to_book_and_user(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();

        $book = $this->createBook($owner);

        $review = Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'モデルテストレビュー',
        ]);

        $this->assertTrue(
            $review->book->is($book)
        );

        $this->assertTrue(
            $review->user->is($reviewer)
        );
    }

    public function test_user_can_have_favorite_books(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user);

        $user->favoriteBooks()->attach($book->id);

        $this->assertTrue(
            $user->favoriteBooks->contains($book)
        );
    }

    public function test_user_can_have_liked_reviews(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $liker = User::factory()->create();

        $book = $this->createBook($owner);

        $review = Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'いいねモデルテスト',
        ]);

        $liker->likedReviews()->attach($review->id);

        $this->assertTrue(
            $liker->likedReviews->contains($review)
        );

        $this->assertTrue(
            $review->likedByUsers->contains($liker)
        );
    }
}