<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            ['user_id' => 1, 'book_id' => 1, 'rating' => 5, 'comment' => '猫の視点で描かれる世界観がユーモラスで、とても楽しめました。'],
            ['user_id' => 2, 'book_id' => 1, 'rating' => 4, 'comment' => '独特の語り口が印象的で、最後まで興味深く読めました。'],
            ['user_id' => 3, 'book_id' => 1, 'rating' => 4, 'comment' => '昔の作品ですが、今読んでも面白さを感じられました。'],

            ['user_id' => 1, 'book_id' => 2, 'rating' => 5, 'comment' => '人との接し方について具体例が多く、とても参考になりました。'],
            ['user_id' => 4, 'book_id' => 2, 'rating' => 4, 'comment' => '仕事だけでなく日常生活でも活かせる内容だと思いました。'],
            ['user_id' => 5, 'book_id' => 2, 'rating' => 5, 'comment' => '何度も読み返したくなる実践的な内容でした。'],

            ['user_id' => 2, 'book_id' => 3, 'rating' => 5, 'comment' => 'コードを書くときに意識すべき点が分かりやすく整理されていました。'],
            ['user_id' => 3, 'book_id' => 3, 'rating' => 4, 'comment' => '具体的なコード例が多く、実務にも役立ちそうです。'],
            ['user_id' => 5, 'book_id' => 3, 'rating' => 5, 'comment' => 'プログラミング初心者にも理解しやすい内容でした。'],

            ['user_id' => 1, 'book_id' => 4, 'rating' => 5, 'comment' => '考え方や習慣を見直すきっかけになる一冊でした。'],
            ['user_id' => 3, 'book_id' => 4, 'rating' => 4, 'comment' => '内容が濃く、少しずつ読み進めるのが良いと感じました。'],
            ['user_id' => 4, 'book_id' => 4, 'rating' => 5, 'comment' => '日々の行動に取り入れたい考え方が多くありました。'],

            ['user_id' => 2, 'book_id' => 5, 'rating' => 4, 'comment' => '説明が丁寧で、内容を理解しやすかったです。'],
            ['user_id' => 4, 'book_id' => 5, 'rating' => 3, 'comment' => '参考になる部分が多かったですが、少し難しい箇所もありました。'],
            ['user_id' => 5, 'book_id' => 5, 'rating' => 4, 'comment' => '基礎から学び直したい人にも良い本だと思います。'],

            ['user_id' => 1, 'book_id' => 6, 'rating' => 4, 'comment' => 'テンポよく読めて、内容もよくまとまっていました。'],
            ['user_id' => 2, 'book_id' => 6, 'rating' => 5, 'comment' => '知らなかった知識が多く、とても勉強になりました。'],
            ['user_id' => 3, 'book_id' => 6, 'rating' => 4, 'comment' => '説明が具体的で、理解しやすかったです。'],

            ['user_id' => 3, 'book_id' => 7, 'rating' => 5, 'comment' => '初心者でも読み進めやすく、実践しやすい内容でした。'],
            ['user_id' => 4, 'book_id' => 7, 'rating' => 4, 'comment' => 'ポイントが整理されていて、復習にも使いやすいです。'],
            ['user_id' => 5, 'book_id' => 7, 'rating' => 4, 'comment' => '基本的な内容をしっかり学ぶことができました。'],

            ['user_id' => 1, 'book_id' => 8, 'rating' => 3, 'comment' => '少し難しかったですが、読み終えると理解が深まりました。'],
            ['user_id' => 2, 'book_id' => 8, 'rating' => 4, 'comment' => '丁寧な解説で、じっくり学びたい人に向いていると思います。'],
            ['user_id' => 5, 'book_id' => 8, 'rating' => 5, 'comment' => '実例が豊富で、とても分かりやすかったです。'],

            ['user_id' => 2, 'book_id' => 9, 'rating' => 4, 'comment' => '内容が整理されていて、スムーズに読むことができました。'],
            ['user_id' => 3, 'book_id' => 9, 'rating' => 5, 'comment' => '実践的な内容が多く、すぐに試してみたくなりました。'],
            ['user_id' => 4, 'book_id' => 9, 'rating' => 4, 'comment' => '具体例が多く、理解を深めやすかったです。'],

            ['user_id' => 1, 'book_id' => 10, 'rating' => 5, 'comment' => '読み終わった後に考え方が変わるような内容でした。'],
            ['user_id' => 4, 'book_id' => 10, 'rating' => 4, 'comment' => '印象に残る部分が多く、楽しんで読むことができました。'],
            ['user_id' => 5, 'book_id' => 10, 'rating' => 3, 'comment' => '少し好みが分かれそうですが、興味深い内容でした。'],

            ['user_id' => 2, 'book_id' => 11, 'rating' => 5, 'comment' => '最後まで飽きずに読むことができ、とても満足しました。'],
            ['user_id' => 3, 'book_id' => 11, 'rating' => 4, 'comment' => '読みやすく、内容もしっかりまとまっていました。'],
        ];

        foreach ($reviews as $review) {
        Review::create($review);
        }
    }
}
