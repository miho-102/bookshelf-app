<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class RankingController extends Controller
{
    public function index()
    {
        $rankedBooks = Book::with('genres')
        ->withAvg('reviews', 'rating')
        ->whereHas('reviews')
        ->orderByDesc('reviews_avg_rating')
        ->take(10)
        ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
