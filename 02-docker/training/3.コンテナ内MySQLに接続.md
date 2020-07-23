# コンテナの中に入る方法

```bash
# DockerComposeで立ち上げた各Dockerの情報を確認
$ docker-compose ps
    Name                  Command               State          Ports
---------------------------------------------------------------------------
docker-mysql      docker-entrypoint.sh mysqld      Up      3306/tcp, 33060/tcp
docker-mysqldata  sh                               Exit 0
docker-php        docker-php-entrypoint apac ...   Up      0.0.0.0:80->80/tcp

# MySQLのコンテナ`docker-mysql`(確認したコンテナ名)に入る（コンテナ内のbashを起動する）
## `docker attach <コンテナ名>` でもコンテナ内に入れるはずだが、Vagrant環境だとフリーズしてしまった
$ docker exec -it docker-mysql bash
# `docker-compose exec mysql bash` でもOK
#（コンテナ名の`docker-mysql`ではなく、サービス名の`mysql`を指定することに注意）

# コンテナ内でbash起動
# MySQLにログイン
% mysql -u root -p
Enter password: # docker-compose.yml で記述したMySQLのログインパスワードを入力
```


------------------------------------------------------------


# PHPからMySQLに接続（コンテナ間通信）

## データベース準備

`blog`データベースに以下のような`articles`テーブルを作る
- AUTOINCREMENT指定のint型`id`カラム
- 200バイト以内のvarchar型`title`カラム
- text型`body`カラム

```bash
$ docker exec -it docker-mysql bash

% mysql -u -p
Enter password: # パスワードを入力してMySQLにログイン

# blogデータベース作成
> create database blog;

# blogデータベースに切り替え
> use blog;

# articlesテーブル作成
> create table articles(id int not null auto_increment, title varchar(200), body text, primary key (id));

# 簡単なブログ記事を1件登録
> insert into articles(title, body) values("The first article", "This is a test page for this blog.");

# テーブルの状態を確認
> select * from articles;
+----+-------------------+------------------------------------+
| id | title             | body                               |
+----+-------------------+------------------------------------+
|  1 | The first article | This is a test page for this blog. |
+----+-------------------+------------------------------------+
```

***

## MySQLに接続するPHPプログラムの準備

Windowsホスト側の `lamp/html/index.php` を以下のように記述
```php
<?php // index.html

const MYSQL_HOST = 'localhost'; // MySQLサーバーのホスト名（後で確認）
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
```

このまま http://localhost にアクセスすると

```
データベース接続失敗: SQLSTATE[HY000] [2002] No such file or directory
```

と表示される

これは、MySQLサーバーのホスト名が正しくないためである

***

## MySQLサーバーのIPアドレスの確認

MySQLサーバーのIPアドレスを確認するために、MySQLコンテナに入る

```bash
$ docker exec -it docker-mysql bash

# コンテナのプライベートIPを調べる
# このコンテナ内ではifconfigコマンドが使えないため、hostnameコマンドを使う
% hostname -i
172.20.0.3  # --> docker-mysqlコンテナの内部IPは 172.20.0.3 と判明

# コンテナから出る
% exit
```

`index.php`内で定義していた`MYSQL_HOST`を、MySQLコンテナのIPアドレスに変更する

```php
// index.php（抜粋）
const MYSQL_HOST = '172.20.0.3'; // MySQLコンテナ内で `hostname -i` コマンドを打った時に返ってきたIPアドレス
```

これで再び http://localhost にアクセスすると

```
array(6) { ["id"]=> int(1) [0]=> int(1) ["title"]=> string(17) "The first article" [1]=> string(17) "The first article" ["body"]=> string(34) "This is a test page for this blog." [2]=> string(34) "This is a test page for this blog." } SmaSurf Quick Search
```

となり、MySQLデータベースからデータを取得できている


------------------------------------------------------------


# コンテナのプライベートIPアドレスを固定する

コンテナを起動するたびにIPアドレスが変わってしまうと、その度に `hostname -i` コマンドで調べなければならなくなる

そのため、コンテナのIPアドレスは固定してしまったほうが良い

`lamp/docker-compose.yml`にネットワークの設定を追加する
```yaml
version: '2'
# Docker Composeアプリケーション・ネットワークの設定
networks:
  app_net:
    driver: bridge # コンテナごとのネットワークをブリッジ接続
    ipam:
     driver: default
     config:
       - subnet: 172.30.0.0/24 # ComposeアプリケーションのネットワークIPを設定
services:
  mysqldata:
    image: busybox
    container_name: docker-mysqldata
    volumes:
      - /var/lib/mysql
  mysql:
    build: ./mysql
    container_name: docker-mysql
    environment:
      MYSQL_ROOT_PASSWORD: pass
    volumes_from:
      - mysqldata
    # MySQLコンテナのネットワーク設定
    networks:
      app_net: # 設定したアプリケーション・ネットワークを使用
        # MySQLのIPアドレスを設定
        ## 上位3組（ネットワーク部）は、アプリケーション・ネットワークのIPアドレスと合わせる（172.30.0）
        ## 下位1組（ホスト部）は、被らない数値にする（今回は 10）
        ipv4_address: 172.30.0.10
  php:
    build: ./php
    container_name: docker-php
    ports:
      - '80:80'
    volumes:
      - ./html:/var/www/html
    depends_on:
      - mysql
    # PHPコンテナのネットワーク設定
    networks:
      app_net:
        ipv4_address: 172.30.0.11 # PHPコンテナIPのホスト部は11にした
```

設定を反映するために、docker-composeを再起動する

```bash
# 各コンテナの停止は`stop`で行う
# `down`してしまうとコンテナが削除されてしまうため、MySQLのデータが消える
$ docker-compose stop
Stopping docker-php   ... done
Stopping docker-mysql ... done

# 各コンテナをリビルドしてバックグラウンドで起動
$ docker-compose up --build -d
Building mysql
 : (略)
Starting docker-mysqldata ... done
Starting docker-mysql     ... done
Starting docker-php       ... done

# MySQLコンテナに入り、IPアドレスを確認してみる
$ docker exec -it docker-mysql bash
% hostname -i
172.30.0.10 # docker-compose.ymlで設定したIPアドレスに固定されている
```

`docker-lamp/html/index.php`を修正し、MySQLのデータを取得できることを確認できたら完了

```php
// index.php（抜粋）
const MYSQL_HOST = '172.30.0.10'; // docker-compose.ymlで設定したMySQLコンテナのIPアドレス
```

---

以上の設定を行ったDockerCompose構成ファイルは、`3-lamp-networks`フォルダにまとめてある


------------------------------------------------------------


# もっと簡単なコンテナ間通信

実は、わざわざアプリケーションネットワークを作らなくてもコンテナ間通信する方法がある

`docker-compose.yml`で、`links`に通信したいコンテナを記述すれば良い

```yaml
version: '2'
services:
  mysqldata:
    image: busybox
    container_name: docker-mysqldata
    volumes:
      - /var/lib/mysql
  mysql:
    build: ./mysql
    container_name: docker-mysql
    environment:
      MYSQL_ROOT_PASSWORD: pass
    volumes_from:
      - mysqldata
  php:
    build: ./php
    container_name: docker-php
    ports:
      - '80:80'
    volumes:
      - ./html:/var/www/html
    depends_on:
      - mysql
    # links設定を追加
    links:
      - mysql # phpコンテナからmysqlコンテナを参照できるようにする    
```

このように設定すると、PHPコンテナからMySQLコンテナへ、ホスト名`mysql`で繋げることが可能になる

この時、ホスト名はコンテナ名ではなく**サービス名**となる

- `lamp/html/index.php`
    ```php
    <?php

    const MYSQL_HOST = 'mysql'; // docker-compose.ymlで設定したMySQLコンテナのサービス名
    const MYSQL_DB = 'blog';
    const MYSQL_USER = 'root';
    const MYSQL_PASSWORD = 'pass';

    try {
        $_ = function($v){return $v;};
        $pdo = new PDO("mysql:host={$_(MYSQL_HOST)};dbname={$_(MYSQL_DB)};charset=utf8",
            MYSQL_USER, MYSQL_PASSWORD, [PDO::ATTR_EMULATE_PREPARES => false]);
    } catch (PDOException $e) {
        exit('データベース接続失敗: '.$e->getMessage());
    }
    var_dump($pdo->query('select * from articles')->fetch());
    ```
