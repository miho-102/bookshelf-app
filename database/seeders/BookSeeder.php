<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'genres' => ['小説'],
                'description' => '夏目漱石による日本文学の名作です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_date' => '1936-10-01',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '人間関係の原則を学べる自己啓発の名著です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_date' => '2012-06-23',
                'genres' => ['技術書'],
                'description' => '読みやすく理解しやすいコードを書くための考え方を学べる技術書です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_date' => '2013-08-30',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '人生や仕事に役立つ7つの習慣を紹介する自己啓発書です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_date' => '1906-04-01',
                'genres' => ['小説'],
                'description' => '正義感の強い主人公を描いた夏目漱石の代表的な小説です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_date' => '2016-09-08',
                'genres' => ['歴史', '科学'],
                'description' => '人類の歴史を幅広い視点から読み解く一冊です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_date' => '2017-12-18',
                'genres' => ['技術書'],
                'description' => '保守しやすいクリーンなコードを書くための原則を解説した技術書です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_date' => '2013-12-13',
                'genres' => ['自己啓発'],
                'description' => 'アドラー心理学を対話形式で分かりやすく紹介する自己啓発書です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_date' => '2015-03-11',
                'genres' => ['小説'],
                'description' => '若手芸人たちの生き方や葛藤を描いた小説です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_date' => '2019-01-11',
                'genres' => ['ビジネス', '科学'],
                'description' => 'データをもとに世界を正しく見るための考え方を紹介する一冊です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_date' => '2007-01-18',
                'genres' => ['ビジネス', '歴史'],
                'description' => 'コンテナが世界の物流や経済をどのように変えたのかを描いた一冊です。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
            ],
        ];

        foreach ($books as $bookData) {
            $genreNames = $bookData['genres'];
            unset($bookData['genres']);

            $book = Book::firstOrCreate(

                ['isbn' => $bookData['isbn']],
                array_merge($bookData, [
                    'user_id' => $user->id,
                ])
            );

            $genreIds = Genre::whereIn('name', $genreNames)
            ->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}
