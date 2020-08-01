<?php // index.html

const MYSQL_HOST = '172.30.0.10'; // docker-compose.ymlで設定したMySQLコンテナのIPアドレス
const MYSQL_DB = 'blog'; // 接続先のデータベース名
const MYSQL_USER = 'root'; // ユーザー名
const MYSQL_PASSWORD = 'pass'; // パスワード（docker-compose.yml で設定したもの）

// PDO::MySQLでデータベース接続
try {
    $_ = function($v){return $v;}; // 定数展開用
    $pdo = new PDO("mysql:host={$_(MYSQL_HOST)};dbname={$_(MYSQL_DB)};charset=utf8",
        MYSQL_USER, MYSQL_PASSWORD, [PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $e) {
    exit('データベース接続失敗: '.$e->getMessage());
}
// articlesテーブルから情報取得
var_dump($pdo->query('select * from articles')->fetch());