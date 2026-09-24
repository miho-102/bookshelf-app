<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private function createBook(User $owner, string $isbn): Book
    {
        return Book::create([
            'user_id' => $owner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => $isbn,
            'published_date' => '2026-09-21',
            'description' => 'お気に入りテスト用です。',
            'image_url' => null,
        ]);
    }

    public function test_authenticated_user_can_add_book_to_favorites(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user, '9781234500001');

        $this->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_authenticated_user_can_remove_book_from_favorites(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user, '9781234500002');

        $user->favoriteBooks()->attach($book->id);

        $this->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_guest_cannot_toggle_favorite(): void
    {
        $owner = User::factory()->create();
        $book = $this->createBook($owner, '9781234500003');

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
        ]);
    }

    public function test_favorites_page_displays_users_favorite_book(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user, '9781234500004');

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertOk();
        $response->assertSee('テスト書籍');
    }
}