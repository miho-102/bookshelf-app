<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    private function createReview(): array
    {
        $owner = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'いいねテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234510001',
            'published_date' => '2026-09-21',
            'description' => 'いいねテスト用です。',
            'image_url' => null,
        ]);

        $reviewer = User::factory()->create();

        $review = Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'いいねテスト用レビューです。',
        ]);

        return [$review, $owner];
    }

    public function test_authenticated_user_can_like_review(): void
    {
        [$review] = $this->createReview();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_authenticated_user_can_remove_like_from_review(): void
    {
        [$review] = $this->createReview();

        $user = User::factory()->create();

        $user->likedReviews()->attach($review->id);

        $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_guest_cannot_like_review(): void
    {
        [$review] = $this->createReview();

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }
}