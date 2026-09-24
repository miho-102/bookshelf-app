<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createBook(User $owner): Book
    {
        return Book::create([
            'user_id' => $owner->id,
            'title' => 'レビューテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234561001',
            'published_date' => '2026-09-21',
            'description' => 'レビューテスト用です。',
            'image_url' => null,
        ]);
    }

    private function createReview(
        User $user,
        Book $book,
        int $rating = 5
    ): Review {
        return Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $rating,
            'comment' => 'テストレビューです。',
        ]);
    }

    public function test_authenticated_user_can_create_review(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);

        $response = $this->actingAs($reviewer)
            ->post(route('reviews.store', $book), [
                'rating' => 5,
                'comment' => 'とても面白い本でした。',
            ]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $reviewer->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);
    }

    public function test_guest_cannot_create_review(): void
    {
        $owner = User::factory()->create();
        $book = $this->createBook($owner);

        $response = $this->post(
            route('reviews.store', $book),
            [
                'rating' => 5,
                'comment' => 'ゲストレビュー',
            ]
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_rating_is_required(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);

        $response = $this->actingAs($reviewer)
            ->post(route('reviews.store', $book), [
                'comment' => '評価なし',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_review_rating_cannot_be_less_than_one(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);

        $response = $this->actingAs($reviewer)
            ->post(route('reviews.store', $book), [
                'rating' => 0,
                'comment' => '評価が小さすぎます。',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_review_rating_cannot_be_greater_than_five(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);

        $response = $this->actingAs($reviewer)
            ->post(route('reviews.store', $book), [
                'rating' => 6,
                'comment' => '評価が大きすぎます。',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_review_comment_is_required(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);

        $response = $this->actingAs($reviewer)
            ->post(route('reviews.store', $book), [
                'rating' => 5,
                'comment' => '',
            ]);

        $response->assertSessionHasErrors('comment');
    }

    public function test_same_user_cannot_review_same_book_twice(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);

        $this->createReview($reviewer, $book);

        $response = $this->actingAs($reviewer)
            ->post(route('reviews.store', $book), [
                'rating' => 4,
                'comment' => '2回目のレビューです。',
            ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_review_owner_can_view_edit_page(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);
        $review = $this->createReview($reviewer, $book);

        $response = $this->actingAs($reviewer)
            ->get(route('reviews.edit', $review));

        $response->assertOk();
    }

    public function test_other_user_cannot_view_review_edit_page(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = $this->createBook($owner);
        $review = $this->createReview($reviewer, $book);

        $response = $this->actingAs($otherUser)
            ->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    public function test_review_owner_can_update_review(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);
        $review = $this->createReview($reviewer, $book);

        $response = $this->actingAs($reviewer)
            ->put(route('reviews.update', $review), [
                'rating' => 4,
                'comment' => '更新後のレビューです。',
            ]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '更新後のレビューです。',
        ]);
    }

    public function test_other_user_cannot_update_review(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = $this->createBook($owner);
        $review = $this->createReview($reviewer, $book);

        $response = $this->actingAs($otherUser)
            ->put(route('reviews.update', $review), [
                'rating' => 1,
                'comment' => '勝手に変更',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'テストレビューです。',
        ]);
    }

    public function test_review_owner_can_delete_review(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $book = $this->createBook($owner);
        $review = $this->createReview($reviewer, $book);

        $response = $this->actingAs($reviewer)
            ->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_other_user_cannot_delete_review(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = $this->createBook($owner);
        $review = $this->createReview($reviewer, $book);

        $response = $this->actingAs($otherUser)
            ->delete(route('reviews.destroy', $review));

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}