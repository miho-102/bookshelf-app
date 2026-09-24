<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        Review::all()->each(function ($review) use ($users) {
            $otherUsers = $users->where('id', '!=', $review->user_id);

            $likeCount = rand(0, 3);

            if ($likeCount === 0) {
                return;
            }

            $likedUserIds = $otherUsers
                ->random(min($likeCount, $otherUsers->count()))
                ->pluck('id')
                ->toArray();

            $review->likedByUsers()->syncWithoutDetaching($likedUserIds);
        });
    }
}
