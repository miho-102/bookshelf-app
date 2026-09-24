<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Review;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Book $book)
    {
        if ($book->reviews()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('books.show', $book)
                ->with('error', 'この書籍にはすでにレビューを投稿しています。');
            }

        $validated = $request->validated();

        Review::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            ]);

            return redirect()->route('books.show', $book)
                ->with('success', 'レビューを投稿しました。');
    }

    public function like(Review $review)
    {
        auth()->user()->likedReviews()->toggle($review->id);

        return back();
    }

    public function edit(Review $review)
    {
        $this->authorize('update', $review);
        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validated();

        $review->update($validated);

        return redirect()->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました。');
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $book = $review->book;

        $review->delete();

        return redirect()->route('books.show', $book)
        ->with('success', 'レビューを削除しました。');
        }
}
