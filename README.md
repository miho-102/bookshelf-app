# BookShelf 書籍レビューアプリ

## 概要

BookShelfは、書籍の登録・レビュー・お気に入り・ランキングなどを管理できる書籍レビューアプリケーションです。

ユーザーは書籍情報を登録し、ジャンルの設定、レビュー投稿、お気に入り登録、レビューへのいいねなどを行うことができます。

### 主な機能

- 会員登録
- ログイン・ログアウト
- 書籍一覧表示
- 書籍詳細表示
- 書籍登録
- 書籍編集・削除
- ジャンル登録・編集・削除
- ジャンル別書籍表示
- レビュー投稿
- レビュー編集・削除
- レビューへのいいね
- お気に入り登録・解除
- お気に入り一覧表示
- レビュー平均評価によるランキング表示
- 公開APIによる書籍情報の取得・登録・更新・削除

## ER図

![ER図](er-diagram.png)

## 環境構築

本プロジェクトは Docker、Laravel Sail を使用して開発環境を構築しています。

### 1. リポジトリをクローン

```bash
git clone git@github.com:miho-102/bookshelf-app.git
cd bookshelf-app
```

### 2. Composerパッケージのインストール

```bash
composer install
```

### 3. 環境ファイルの作成

`.env.example` をコピーして `.env` を作成します。

```bash
cp .env.example .env
```

`.env` のデータベース接続情報が以下の内容になっていることを確認します。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

※ `DB_HOST` は `localhost` や `127.0.0.1` ではなく、Dockerコンテナ名である `mysql` を指定します。

### 4. Laravel Sailの起動

```bash
./vendor/bin/sail up -d
```

必要に応じて、以下のようにSailのエイリアスを設定できます。

```bash
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc
exec $SHELL
```

以降、エイリアスを設定した場合は `sail` でコマンドを実行できます。

### 5. アプリケーションキーの生成

```bash
./vendor/bin/sail artisan key:generate
```

### 6. データベースのマイグレーション・初期データ投入

```bash
./vendor/bin/sail artisan migrate --seed
```

既存のデータベースをリセットして再構築する場合は、以下を実行します。

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### 7. フロントエンド環境のセットアップ

NPM依存パッケージをインストールします。

```bash
./vendor/bin/sail npm install
```

フロントエンドの開発サーバーを起動します。

```bash
./vendor/bin/sail npm run dev
```

本プロジェクトでは、Vite、Tailwind CSS、Alpine.jsを使用しています。

### 8. phpMyAdmin

phpMyAdminは `compose.yaml` に設定済みです。

Laravel Sail起動後、以下のURLからアクセスできます。

```text
http://localhost:8080
```

接続先にはMySQLコンテナを使用します。

## 使用技術

- PHP 8.5.8
- Laravel 10.50.3
- Laravel Fortify
- Laravel Sail
- MySQL 8.4
- Docker
- phpMyAdmin
- Vite
- Tailwind CSS
- Alpine.js

## 作成者

小林 美穂

## APIエンドポイント一覧

| メソッド | エンドポイント         | 概要           |
| -------- | ---------------------- | -------------- |
| GET      | `/api/v1/books`        | 書籍一覧を取得 |
| GET      | `/api/v1/books/{book}` | 書籍詳細を取得 |
| POST     | `/api/v1/books`        | 書籍を登録     |
| PUT      | `/api/v1/books/{book}` | 書籍情報を更新 |
| DELETE   | `/api/v1/books/{book}` | 書籍を削除     |

書籍一覧APIでは、キーワード検索、ジャンルによる絞り込み、ページネーションに対応しています。

## 開発環境URL

- アプリケーション：`http://localhost`
- phpMyAdmin：`http://localhost:8080`

## テスト用ログイン情報

```text
メールアドレス：yamada@example.com
パスワード：password
```

## テスト

テストは以下のコマンドで実行できます。

```bash
./vendor/bin/sail artisan test
```

カバレッジを確認する場合は、以下を実行します。

```bash
./vendor/bin/sail artisan test --coverage
```

### テスト結果

- 97 tests passed
- 225 assertions
- Coverage: 85.9%
