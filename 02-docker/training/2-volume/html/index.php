<?php
// MySQLサーバ接続設定
$db = [
    // dbサービスコンテナとリンクしているため、tcp://db:3306 で service://db:3306 に接続可能
    'host' => 'db',
    'database' => 'training2', // 接続先データベース
    'user' => 'root', // 接続ユーザ
    'password' => 'root', // 接続パスワード
];

// MySQLサーバ接続
try {
    $dbh = new PDO("mysql:dbname={$db['database']};host={$db['host']}", $db['user'], $db['password']);
    echo '<p>データベースに接続しました</p>';
} catch (PDOException $e) {
    echo '<p>接続失敗: ' . $e->getMessage() . '</p>';
    exit();
}

// usersテーブルがなければ新規作成する
try {
    $dbh->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER AUTO_INCREMENT NOT NULL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created DATETIME,
        updated DATETIME
    )');
} catch (PDOException $e) {
    echo '<p>' . $e->getMessage() . '</p>';
    exit();
}

// usersテーブルからデータ取得
$rows = $dbh->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);

// usersテーブルに一つもデータがない場合は新規データ挿入
if (count($rows) === 0) {
    $sql = $dbh->prepare('INSERT INTO users (name, password, created, updated) VALUES (:name, :password, :created, :updated)');
    // 各カラムの値を指定して挿入実行
    // ※ password は BCRYPT ハッシュ化して保存する
    if ($sql->execute([
        ':name' => 'admin',
        ':password' => password_hash('pa$$word', PASSWORD_BCRYPT),
        ':created' => date('Y-m-d H:i:s'),
        ':updated' => date('Y-m-d H:i:s'),
    ])) {
        echo '<p>新規データを挿入しました</p>';
    } else {
        exit('<p>データの挿入に失敗しました</p>');
    }
    //  改めてusersテーブルからデータ取得
    $rows = $dbh->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
}
?>

<table border="1">
    <thead>
        <tr>
            <th>id</th>
            <th>name</th>
            <th>password</th>
            <th>created</th>
            <th>updated</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['password'] ?></td>
                <td><?= $row['created'] ?></td>
                <td><?= $row['updated'] ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>