<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookAccessTest extends TestCase
{
    use RefreshDatabase;


    public function test_guest_cannot_access_book_create_page(): void
    {
        $response = $this->get('/books/create');
        $response->assertRedirect('/login');
    }

    public function test_guest_can_access_book_index_page(): void
    {
        $response = $this->get('/books');
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_favorites_page(): void
    {
        $response = $this->get('/favorites');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_genres_page(): void
    {
        $response = $this->get('/genres');
        $response->assertRedirect('/login');
    }

    public function test_guest_can_access_ranking_page(): void
    {
        $response = $this->get('/ranking');
        $response->assertStatus(200);
    }

    public function test_guest_can_access_book_detail_page(): void
    {
        $user = User::factory()->create();

        $book = \App\Models\Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-09-19',
            'description' => 'テスト用の書籍です。',
            'image_url' => null,
        ]);

        $response = $this->get('/books/' . $book->id);
        $response->assertStatus(200);
        }

    public function test_user_cannot_edit_another_users_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '他人の書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567891',
            'published_date' => '2026-09-19',
            'description' => '権限テスト用です。',
            'image_url' => null,
            ]);

        $response = $this
            ->actingAs($otherUser)
            ->get('/books/' . $book->id . '/edit');

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '削除権限テスト',
            'author' => 'テスト著者',
            'isbn' => '9781234567892',
            'published_date' => '2026-09-21',
            'description' => '削除権限のテスト用です。',
            'image_url' => null,
            ]);

        $response = $this
            ->actingAs($otherUser)
            ->delete('/books/' . $book->id);
            
        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::create([
            'name' => 'テストジャンル',
            ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-09-21',
            'description' => 'テスト用です。',
            'image_url' => null,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->put(route('books.update', $book), [
            'title' => '勝手に更新',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-09-21',
            'description' => '勝手に更新',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertForbidden();
    }

    public function test_review_rating_cannot_be_less_than_one(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'レビューテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567894',
            'published_date' => '2026-09-21',
            'description' => 'レビューのバリデーションテスト用です。',
            'image_url' => null,
            ]);

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 0,
                'comment' => 'テストレビューです。',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_user_cannot_review_same_book_twice(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '重複レビューテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567895',
            'published_date' => '2026-09-21',
            'description' => '重複レビューのテスト用です。',
            'image_url' => null,
            ]);

        $book->reviews()->create([
        'user_id' => $user->id,
        'rating' => 5,
        'comment' => '1回目のレビューです。',
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('reviews.store', $book), [
            'rating' => 4,
            'comment' => '2回目のレビューです。',
        ]);

    $response->assertSessionHas('error');

    $this->assertDatabaseCount('reviews', 1);
    }

}
