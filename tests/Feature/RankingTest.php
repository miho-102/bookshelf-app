<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    private function createBook(
        User $owner,
        string $title,
        string $isbn
    ): Book {
        return Book::create([
            'user_id' => $owner->id,
            'title' => $title,
            'author' => 'テスト著者',
            'isbn' => $isbn,
            'published_date' => '2026-09-21',
            'description' => 'ランキングテスト用です。',
            'image_url' => null,
        ]);
    }

    public function test_guest_can_view_ranking_page(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();
    }

    public function test_books_are_ranked_by_average_rating_descending(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();

        $highBook = $this->createBook(
            $owner,
            '高評価の本',
            '9781234530001'
        );

        $lowBook = $this->createBook(
            $owner,
            '低評価の本',
            '9781234530002'
        );

        Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $highBook->id,
            'rating' => 5,
            'comment' => '高評価レビュー',
        ]);

        Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $lowBook->id,
            'rating' => 3,
            'comment' => '低評価レビュー',
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertSeeInOrder([
            '高評価の本',
            '低評価の本',
        ]);
    }

    public function test_book_without_reviews_is_not_in_ranking(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();

        $reviewedBook = $this->createBook(
            $owner,
            'レビューありの本',
            '9781234530003'
        );

        $noReviewBook = $this->createBook(
            $owner,
            'レビューなしの本',
            '9781234530004'
        );

        Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $reviewedBook->id,
            'rating' => 5,
            'comment' => 'レビューあり',
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertSee('レビューありの本');
        $response->assertDontSee('レビューなしの本');
    }

    public function test_ranking_displays_only_top_ten_books(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $book = $this->createBook(
                $owner,
                'ランキング本' . $i,
                '978123454' . str_pad(
                    (string) $i,
                    4,
                    '0',
                    STR_PAD_LEFT
                )
            );

            Review::create([
                'user_id' => $reviewer->id,
                'book_id' => $book->id,
                'rating' => $i === 11 ? 1 : 5,
                'comment' => 'ランキングテスト',
            ]);
        }

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertDontSee('ランキング本11');
    }
}