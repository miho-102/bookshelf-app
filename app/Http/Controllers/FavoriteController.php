<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function toggle(Book $book)
    {
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back();
    }

    public function index()
    {
        $books = auth()->user()
        ->favoriteBooks()
        ->with('genres')
        ->withAvg('reviews', 'rating')
        ->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
