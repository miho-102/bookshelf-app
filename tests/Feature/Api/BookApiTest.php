<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private function createBook(
        User $user,
        string $isbn = '9781234570001',
        string $title = 'APIテスト書籍'
    ): Book {
        return Book::create([
            'user_id' => $user->id,
            'title' => $title,
            'author' => 'APIテスト著者',
            'isbn' => $isbn,
            'published_date' => '2026-09-21',
            'description' => 'APIテスト用です。',
            'image_url' => null,
        ]);
    }

    private function validApiData(
        User $user,
        Genre $genre,
        array $overrides = []
    ): array {
        return array_merge([
            'user_id' => $user->id,
            'title' => 'API新規書籍',
            'author' => 'API著者',
            'isbn' => '9781234570002',
            'published_date' => '2026-09-21',
            'description' => 'API登録テスト用です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ], $overrides);
    }

    public function test_api_can_get_book_list(): void
    {
        $user = User::factory()->create();

        $this->createBook(
            $user,
            '9781234570003',
            'API一覧テスト'
        );

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'API一覧テスト',
        ]);
    }

    public function test_api_can_get_book_detail(): void
    {
        $user = User::factory()->create();

        $book = $this->createBook(
            $user,
            '9781234570004',
            'API詳細テスト'
        );

        $response = $this->getJson(
            '/api/v1/books/' . $book->id
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $book->id,
            'title' => 'API詳細テスト',
        ]);
    }

    public function test_api_returns_404_for_missing_book(): void
    {
        $response = $this->getJson('/api/v1/books/999999');

        $response->assertNotFound();
    }

    public function test_api_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'APIジャンル',
        ]);

        $response = $this->postJson(
            '/api/v1/books',
            $this->validApiData($user, $genre)
        );

        $response->assertCreated();

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'API新規書籍',
            'isbn' => '9781234570002',
        ]);

        $book = Book::where(
            'isbn',
            '9781234570002'
        )->firstOrFail();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_api_create_requires_required_fields(): void
    {
        $response = $this->postJson(
            '/api/v1/books',
            []
        );

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'user_id',
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);
    }

    public function test_api_create_requires_existing_user(): void
    {
        $genre = Genre::create([
            'name' => 'APIジャンル',
        ]);

        $user = User::factory()->create();

        $data = $this->validApiData(
            $user,
            $genre,
            [
                'user_id' => 999999,
            ]
        );

        $response = $this->postJson(
            '/api/v1/books',
            $data
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('user_id');
    }

    public function test_api_create_requires_thirteen_character_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'APIジャンル',
        ]);

        $response = $this->postJson(
            '/api/v1/books',
            $this->validApiData(
                $user,
                $genre,
                ['isbn' => '12345']
            )
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_api_create_requires_unique_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'APIジャンル',
        ]);

        $this->createBook(
            $user,
            '9781234570005'
        );

        $response = $this->postJson(
            '/api/v1/books',
            $this->validApiData(
                $user,
                $genre,
                ['isbn' => '9781234570005']
            )
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_api_create_requires_at_least_one_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'APIジャンル',
        ]);

        $response = $this->postJson(
            '/api/v1/books',
            $this->validApiData(
                $user,
                $genre,
                ['genres' => []]
            )
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genres');
    }

    public function test_api_create_image_url_must_be_valid_url(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'APIジャンル',
        ]);

        $response = $this->postJson(
            '/api/v1/books',
            $this->validApiData(
                $user,
                $genre,
                ['image_url' => 'not-a-url']
            )
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('image_url');
    }

    public function test_api_can_update_book(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '更新ジャンル',
        ]);

        $book = $this->createBook(
            $user,
            '9781234570006'
        );

        $response = $this->putJson(
            '/api/v1/books/' . $book->id,
            $this->validApiData(
                $user,
                $genre,
                [
                    'title' => 'API更新後書籍',
                    'isbn' => '9781234570006',
                ]
            )
        );

        $response->assertOk();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'API更新後書籍',
            'isbn' => '9781234570006',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_api_update_can_keep_same_isbn(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'APIジャンル',
        ]);

        $book = $this->createBook(
            $user,
            '9781234570007'
        );

        $response = $this->putJson(
            '/api/v1/books/' . $book->id,
            $this->validApiData(
                $user,
                $genre,
                [
                    'isbn' => '9781234570007',
                ]
            )
        );

        $response->assertOk();
        $response->assertJsonMissingValidationErrors();
    }

    public function test_api_update_cannot_use_another_books_isbn(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'APIジャンル',
        ]);

        $book = $this->createBook(
            $user,
            '9781234570008'
        );

        $otherBook = $this->createBook(
            $user,
            '9781234570009'
        );

        $response = $this->putJson(
            '/api/v1/books/' . $book->id,
            $this->validApiData(
                $user,
                $genre,
                [
                    'isbn' => $otherBook->isbn,
                ]
            )
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_api_can_delete_book(): void
    {
        $user = User::factory()->create();

        $book = $this->createBook(
            $user,
            '9781234570010'
        );

        $response = $this->deleteJson(
            '/api/v1/books/' . $book->id
        );

        $response->assertOk();

        $response->assertJson([
            'message' => '書籍を削除しました。',
        ]);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_api_index_validates_per_page(): void
    {
        $response = $this->getJson(
            '/api/v1/books?per_page=0'
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('per_page');
    }

    public function test_api_index_validates_page(): void
    {
        $response = $this->getJson(
            '/api/v1/books?page=0'
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('page');
    }

    public function test_api_index_validates_genre_id(): void
    {
        $response = $this->getJson(
            '/api/v1/books?genre_id=999999'
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genre_id');
    }

    public function test_api_can_filter_books_by_keyword(): void
    {
        $user = User::factory()->create();

        $this->createBook(
            $user,
            '9781234570011',
            'Laravel入門'
        );

        $this->createBook(
            $user,
            '9781234570012',
            'PHP基礎'
        );

        $response = $this->getJson(
            '/api/v1/books?keyword=Laravel'
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'Laravel入門',
        ]);
        $response->assertJsonMissing([
            'title' => 'PHP基礎',
        ]);
    }

    public function test_api_can_filter_books_by_genre(): void
    {
        $user = User::factory()->create();

        $genreA = Genre::create([
            'name' => '小説',
        ]);

        $genreB = Genre::create([
            'name' => '技術書',
        ]);

        $bookA = $this->createBook(
            $user,
            '9781234570013',
            '小説の本'
        );

        $bookB = $this->createBook(
            $user,
            '9781234570014',
            '技術の本'
        );

        $bookA->genres()->attach($genreA->id);
        $bookB->genres()->attach($genreB->id);

        $response = $this->getJson(
            '/api/v1/books?genre_id=' . $genreA->id
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => '小説の本',
        ]);
        $response->assertJsonMissing([
            'title' => '技術の本',
        ]);
    }
}