<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookDetailResource;
use App\Http\Requests\ApiBookIndexRequest;
use App\Http\Requests\ApiStoreBookRequest;
use App\Http\Requests\ApiUpdateBookRequest;

class BookController extends Controller
{
    public function index(ApiBookIndexRequest $request)
    {
        $validated = $request->validated();

        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if (!empty($validated['keyword'])) {
            $keyword = $validated['keyword'];

            $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('author', 'like', '%' . $keyword . '%');
              });
        }

        if (!empty($validated['genre_id'])) {
            $genreId = $validated['genre_id'];

            $query->whereHas('genres', function ($q) use ($genreId) {
            $q->where('genres.id', $genreId);
            });
        }

        $perPage = $validated['per_page'] ?? 10;

        $books = $query->latest()->paginate($perPage);

        return BookResource::collection($books);
        }

    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
            ]);

        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');

        return new BookDetailResource($book);
    }

    public function store(ApiStoreBookRequest $request)
    {
        $validated = $request->validated();

        $genres = $validated['genres'] ?? [];
        unset($validated['genres']);

        $book = Book::create($validated);

        $book->genres()->sync($genres);

        $book->load('genres');
        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');

        return (new BookResource($book))
        ->response()
        ->setStatusCode(201);

    }

    public function update(ApiUpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();

        $genres = $validated['genres'] ?? [];
        unset($validated['genres']);

        $book->update($validated);

        $book->genres()->sync($genres);

        $book->load('genres');
        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');

        return new BookResource($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json([
            'message' => '書籍を削除しました。'
            ], 200);
    }
}
