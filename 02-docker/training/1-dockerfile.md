# Dockerfileで始めるIaC

## Dockerfileについて

これまでは、DockerHubに登録されているイメージをそのまま使っていた

しかし、デフォルトの設定を変更したい場合や、追加パッケージが必要な場合なども多いはずで、そういった場合は困ってしまう

（例えば `php:7.4-apache` はデフォルトのままだと php-pdo エクステンション等は入っていないため、データベース接続ができなかったりする）

起動中のコンテナの中に入って必要な設定や導入作業を行っても良いのだが、その場合、そのコンテナを別の環境に持ち込みたいときや誤ってコンテナを削除してしまったときに、同じ作業を繰り返さなくてはならなくなるため非常に効率が悪い

こうした問題を解決するために使うのが `Dockerfile` である

- **Dockerfile**
    - 公開されているDockerイメージをそのまま使うのではなく、必要なパッケージやアプリ、各種設定を含んだDockerイメージを自分で作成して使用する場合に記述するビルド手順書

### 基本的な書き方
```ruby
# FROM: どのイメージを基にするか
FROM centos

# MAINTAINER: 作成したユーザの情報（任意）
MAINTAINER Admin <admin@admin.com>

# RUN: docker buildするときに実行される
RUN echo "now building..." && \
    curl -SL http://example.com/postgres-$PG_VERSION.tar.xz | tar -xJC /usr/src/postgress

# COPY: ホスト側にあるファイル（Dockerfileのあるディレクトリ配下にあるファイルのみ）をコンテナ内にコピー
COPY ./php.ini /usr/local/etc/php/

# ADD: COPYとほぼ同じだが、tarファイルは自動で展開してくれる
ADD ./vhosts.tar /etc/nginx/conf.d/

# EXPOSE: コンテナが接続用にリッスンするポートを指定
## 要するにコンテナに開ける穴となるポートを指定
EXPOSE 80

# ENV: コンテナ内の環境変数を設定
## 基本的に Dockerfile: RUN コマンド内で参照可能な環境変数を設定
ENV PATH /usr/local/postgres-$PG_MAJOR/bin:$PATH

# USER: コンテナ内でコマンドを実行するユーザを指定
USER www-data

# WORKDIR: 作業ディレクトリを変更する
WORKDIR /var/www/

# CMD: docker run (docker-compose up | start) するときに実行される
## 以下の場合 $ /bin/bash -c "echo 'now running...'" が実行される
CMD ["/bin/bash", "-c", "echo 'now running...'"]
```

### Dockerfileを書く場合のポイントについて

#### RUNコマンドの挙動について
`Dockerfile` に書かれた `RUN` コマンドは、毎回中間的なDockerコンテナとして起動し、各コマンドを実行した段階でDockerイメージを作成する、という作業を繰り返す

この各段階のDockerイメージは**レイヤー**と呼ばれる

そのため、一つ前のコマンドの実行状態を継承するわけではないことに気をつける必要がある

例えば「`/tmp` ディレクトリに移動して `test.txt` を作成」したい場合、以下のように書いても上手く行かない

```ruby
RUN cd /tmp           # (1)
RUN touch test.txt    # (2)
```

(1) 実行時、起動した中間コンテナで `cd /tmp` コマンドが実行されて作業ディレクトリは `/tmp` に変更されるが、(2) 実行時はまた別の中間コンテナが起動するため、作業ディレクトリは `/` にリセットされてしまう

そのため、上記作業を実現したい場合は以下のように書く必要がある

```ruby
# シェルスクリプトの && でつなげる
RUN cd /tmp && touch test.txt

# もしくは、

# WORKDIR で作業ディレクトリ変更
WORKDIR /tmp
RUN touch test.txt
```

#### RUNコマンドはなるべく最小限で
前述の話と被るが、RUNコマンドは実行の度にレイヤーが積み重なっていくため、イメージが肥大化しやすい

そのため極力 RUNコマンドは一つにまとめるように書くことを推奨している

また、作成できるレイヤーには上限（128レイヤー）があるため、その意味でもなるべくまとめて実行したほうがよい

```ruby
# RUNコマンド書き方のコツ
## - コマンドは && でつなげていく
## - \ で改行できるため有効活用する
## - RUNコマンド途中でコメントを書きたい場合は :'コメント' コマンドを活用する
RUN : '必要なパッケージのインストール' && \
    apt-get update && apt-get install -y wget curl git libicu-dev mailutils unzip vim && \
    : 'PHPエクステンションの導入' && \
    docker-php-ext-install intl pdo pdo_mysql && \
    : 'cleanup apt-get caches' && \
    apt-get clean && rm -rf /var/lib/apt/lists/*
```

#### Dockerビルド時のキャッシュについて
Dockerコンテナをリビルドをする際、`Dockerfile` ですでに実行済みのコマンドは、キャッシュが使用される

そのため、あまり変わらない部分については、`Dockerfile` の最初のほうに書いておくことで、Dockerビルドの時間が短縮できる

ここで言う「あまり変わらない部分」というのは、例えば基本必要となるパッケージのインストールなどである

#### Linuxパッケージマネージャのキャッシュについて
Linuxパッケージマネージャ（Debian の `apt-get`, CentOS の `yum`, Alpine の `apk`）を利用して必要なパッケージをインストールした場合、パッケージマネージャのキャッシュファイルが残ったままだと、Dockerイメージ肥大化の原因になる

そのため、必要なパッケージをインストールした後はキャッシュ削除しておくと良い（`apk` だと `--no-cache` オプションが使えるので便利）

***

## Infrastructure as Code (IaC), CI/CD

`Dockerfile` を作成することで、自分で好きなようにDockerイメージを作成することができるようになるが、メリットはそれだけでなく、**OS設定などのインフラ構築部分も含めコード化できる**、というのが非常に大きい

これにより、同じ環境を簡単にミスなく作ることができ、またインフラ構築処理も構成管理することが可能になる

例えば Git で `Dockerfile` をバージョン管理し、CIツール（Jenkins などの自動デプロイツール）を使えば、アプリケーションのビルドからDockerビルド（Dockerイメージの作成）、コンテナのデプロイまでを自動化することができる

さらに、デプロイした環境に対して自動テスト（Seleniumなど）を連携させることで、より効率的に精度の高いシステム開発ができるようになる

このように開発すれば、ソースコードや環境構築部分で変更が入った際、簡単に本番に近い状態でデプロイ＆テストができる

本番に近い状態で常にテストが成功していれば、リリース直前で問題が発生することも減らすことができるようになる

このような考え方を **Infrastructure as Code (IaC)** と呼ぶ

こうして、ソースコードのバージョン管理が Git で可能になったように、OSレベルの設定管理が `Dockerfile` で可能になったとも言える

***

## Dockerfileを加えたLAMP構成

### 演習課題
ここまでの話を踏まえて、docker-compose を使ったLAMP構成を作り直す

コンテナ構成とビルド手順は以下の通り

- composeファイルバージョン: `3`
- `web`サービス（コンテナ名: `training2_web`）
    - ベースイメージ: `php:7.4-apache`
    - ログフォーマット: `json-file`
    - ポート: http://localhost でホスト側からアクセスできるように設定
    - リンク: `db`サービス
    - 必要パッケージ:
        - aptパッケージ: `libonig-dev`
            - ※ PHP 7.4 からマルチバイト文字関連操作に `mbstring` エクステンションではなく、鬼車を使うように変更されたため導入する
        - PHPエクステンション: `pdo`, `pdo_mysql`
            - ※ `docker-php-ext-install` コマンドを使うのが簡単
    - 設定:
        - PHP設定ファイル: コンテナ内 `/usr/local/etc/php/php.ini` に配置
            - date.timezone: `Asia/Tokyo`
            - mbstring.internal_encoding: `UTF-8`
            - mbstring.language: `Japanese`
- `db`サービス（コンテナ名: `training2_db`）
    - ベースイメージ: `mysql:5.7`
    - ログフォーマット: `json-file`
    - rootユーザパスワード: `root`
    - 設定ファイル: コンテナ内 `/etc/mysql/conf.d/my.cnf` に配置
        - mysqld.character-set-server: `utf8mb4`
        - client.default-character-set: `utf8mb4`
    - ※ my.cnf はパーミッション 644 に設定すること

### 構成
構成は案の一つである（参照: [2-lamp](./2-lamp)）

```bash
2-lamp/
|_ docker/ # dockerコンテナ設定
|  |_ db/
|  |  |_ Dockerfile # dbサービスのビルド手順
|  |  |_ my.cnf # MySQL設定ファイル => service://db:/etc/mysql/conf.d/my.cnf
|  |
|  |_ web/
|     |_ Dockerfile # webサービスのビルド手順
|     |_ php.ini # PHP設定ファイル => service://web:/usr/local/etc/php/php.ini
|
|_ docker-compose.yml # dockerコンテナ構成
                      # - web: php:7.4-apache
                      #   - http://localhost => service://web:80
                      #   - link: service://db:3306
                      # - db: mysql:5.7
```

各ファイルの中身の解説については、参照ディレクトリ内のそれぞれのファイル内のコメントを見ること

### 動作確認
```bash
# -- user@localhost

# docker-compose.yml のあるディレクトリに移動する
# $ cd /path/to/2-lamp/

# docker-compose.yml に記述してある全てのサービス（コンテナ）をビルドする
$ docker-compose build

# docker-compose.yml に記述してある全てのサービス（コンテナ）を起動する
## -d: daemonモード（バックグラウンド）でコンテナ起動
$ docker-compose up -d

# 起動確認
$ docker-compose ps
    Name                   Command               State          Ports       
----------------------------------------------------------------------------
training2_db    docker-entrypoint.sh mysqld      Up      3306/tcp, 33060/tcp
training2_web   docker-php-entrypoint apac ...   Up      0.0.0.0:80->80/tcp 

# webサービスコンテナに入る（コンテナ内 bash コマンド実行）
$ docker-compose exec web bash

# -- root@service://web

# --- php.ini の内容が反映されているか確認する ---

# date.timezone 確認 => Asia/Tokyo になっていればOK
% php -i | grep 'date.timezone'
date.timezone => Asia/Tokyo => Asia/Tokyo

# mbstring 設定確認
## internal_encoding => UTF-8, language => Japanese ならOK
% php -i | grep 'mbstring'
 :
mbstring.internal_encoding => UTF-8 => UTF-8
mbstring.language => Japanese => Japanese
 :

# コンテナ抜ける
% exit

# -- user@localhost

# dbサービスコンテナに入る（コンテナ内 bash コマンド実行）
$ docker-compose exec db bash

# -- root@service://db

# user: root, password: root でログインできるか確認
% mysql -u'root' -p'root'

# MySQLの文字コード設定が反映されているか確認
## character_set_client, character_set_server 等が utf8mb4 になっていればOK
> show variables like '%char%';
+--------------------------+----------------------------+
| Variable_name            | Value                      |
+--------------------------+----------------------------+
| character_set_client     | utf8mb4                    |
| character_set_connection | utf8mb4                    |
| character_set_database   | utf8mb4                    |
| character_set_filesystem | binary                     |
| character_set_results    | utf8mb4                    |
| character_set_server     | utf8mb4                    |
| character_set_system     | utf8                       |
| character_sets_dir       | /usr/share/mysql/charsets/ |
+--------------------------+----------------------------+

# MySQLサーバから抜ける
> exit

# コンテナから抜ける
% exit

# -- user@localhost

# 後片付け
## docker-compose 内の全コンテナを停止 => 削除
$ docker-compose down
```
