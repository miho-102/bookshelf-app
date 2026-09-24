<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private function validBookData(Genre $genre, array $overrides = []): array
    {
        return array_merge([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234550001',
            'published_date' => '2026-09-21',
            'description' => '書籍テスト用です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ], $overrides);
    }

    private function createBook(User $user, string $isbn = '9781234550002'): Book
    {
        return Book::create([
            'user_id' => $user->id,
            'title' => '既存書籍',
            'author' => '既存著者',
            'isbn' => $isbn,
            'published_date' => '2026-09-21',
            'description' => '既存書籍です。',
            'image_url' => null,
        ]);
    }

    public function test_guest_can_view_book_index(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertOk();
    }

    public function test_guest_can_view_book_detail(): void
    {
        $owner = User::factory()->create();
        $book = $this->createBook($owner);

        $response = $this->get(route('books.show', $book));

        $response->assertOk();
        $response->assertSee('既存書籍');
    }

    public function test_guest_cannot_access_book_create_page(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);

        $response = $this->actingAs($user)
            ->post(route('books.store'), $this->validBookData($genre));

        $book = Book::where('isbn', '9781234550001')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'isbn' => '9781234550001',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_book_requires_title_author_isbn_date_and_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), []);

        $response->assertSessionHasErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);
    }

    public function test_isbn_must_be_thirteen_characters(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);

        $response = $this->actingAs($user)
            ->post(
                route('books.store'),
                $this->validBookData($genre, [
                    'isbn' => '12345',
                ])
            );

        $response->assertSessionHasErrors('isbn');
    }

    public function test_isbn_must_be_unique_when_creating_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);

        $this->createBook($user, '9781234550003');

        $response = $this->actingAs($user)
            ->post(
                route('books.store'),
                $this->validBookData($genre, [
                    'isbn' => '9781234550003',
                ])
            );

        $response->assertSessionHasErrors('isbn');
    }

    public function test_image_url_must_be_valid_url(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);

        $response = $this->actingAs($user)
            ->post(
                route('books.store'),
                $this->validBookData($genre, [
                    'image_url' => 'これはURLではありません',
                ])
            );

        $response->assertSessionHasErrors('image_url');
    }

    public function test_at_least_one_genre_is_required(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);

        $response = $this->actingAs($user)
            ->post(
                route('books.store'),
                $this->validBookData($genre, [
                    'genres' => [],
                ])
            );

        $response->assertSessionHasErrors('genres');
    }

    public function test_owner_can_update_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '更新ジャンル']);
        $book = $this->createBook($user, '9781234550004');

        $response = $this->actingAs($user)
            ->put(
                route('books.update', $book),
                $this->validBookData($genre, [
                    'title' => '更新後の書籍',
                    'isbn' => '9781234550004',
                ])
            );

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後の書籍',
            'isbn' => '9781234550004',
        ]);
    }

    public function test_update_can_keep_same_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);
        $book = $this->createBook($user, '9781234550005');

        $response = $this->actingAs($user)
            ->put(
                route('books.update', $book),
                $this->validBookData($genre, [
                    'isbn' => '9781234550005',
                ])
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '9781234550005',
        ]);
    }

    public function test_update_cannot_use_another_books_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);

        $book = $this->createBook($user, '9781234550006');
        $otherBook = $this->createBook($user, '9781234550007');

        $response = $this->actingAs($user)
            ->put(
                route('books.update', $book),
                $this->validBookData($genre, [
                    'isbn' => $otherBook->isbn,
                ])
            );

        $response->assertSessionHasErrors('isbn');
    }

    public function test_owner_can_delete_book(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user, '9781234550008');

        $response = $this->actingAs($user)
            ->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_other_user_cannot_edit_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = $this->createBook($owner, '9781234550009');

        $response = $this->actingAs($otherUser)
            ->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    public function test_other_user_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);
        $book = $this->createBook($owner, '9781234550010');

        $response = $this->actingAs($otherUser)
            ->put(
                route('books.update', $book),
                $this->validBookData($genre, [
                    'isbn' => '9781234550010',
                ])
            );

        $response->assertForbidden();
    }

    public function test_other_user_cannot_delete_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = $this->createBook($owner, '9781234550011');

        $response = $this->actingAs($otherUser)
            ->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}