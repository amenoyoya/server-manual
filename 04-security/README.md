# WEBセキュリティ入門

## LAMP環境構築Docker

### Environment
- Shell: `bash`
- Docker: `19.03.12`
    - docker-compose: `1.26.0`

### Initialize
`lamp` スクリプトを用いてLAMP環境構築用のDocker構成を展開できる

```bash
# ./lamp に実行権限付与
$ chmod +x ./lamp

# Dockerテンプレート展開
$ ./lamp init

# => 以下のようにファイル展開される
```

### Structure
```bash
./
|_ docker/ # Dockerコンテナ設定
|  |_ db/ # db service container
|  |  |_ dump/ # mount => service://db:/var/dump/ (ダンプデータ等のやり取り用)
|  |  |_ initdb.d/ # mount => service://db:/docker-entrypoint-initdb.d/
|  |  |            # この中に配置した *.sql ファイルで初期データベースを構成可能
|  |  |_ my.cnf # mount => service://db:/etc/mysql/conf.d/my.cnf:ro (MySQL設定ファイル)
|  |
|  |_ web/ # web service container
|     |_ conf/
|     |  |_ 000-default.conf # mount => service://web:/etc/apache2/sites-available/000-default.conf (Apache設定ファイル)
|     |  |_ php.ini # mount => service://web:/etc/php.d/php.ini (PHP設定ファイル)
|     |
|     |_ Dockerfile # web service container build setting file
|
|_ www/ # mount => service://web:/var/www/ (~www-data)
|  |_ app/ # プロジェクトディレクトリ
|  |   |_ public/ # DocumentRoot
|  |
|  |_ .msmtprc # SMTPサーバ接続設定ファイル
|  |_ startup.sh # web service container が開始したときに実行されるスクリプト (Apache実行等)
|
|_ .env # 環境変数設定ファイル (Dockerコンテナ実行ポート定義)
|_ docker-compose.yml # docker-compose setting file
```

### Docker containers
- networks:
    - **lampnet**: `bridge`
        - この環境におけるDockerコンテナは全てこのネットワークに属する
- volumes:
    - **db-data**: `local`
        - db service container データ永続化用
- services:
    - **web**: `php:7.4-apache`
        - PHP + Apache Web Server
        - http://localhost:8080 => service://web:80
            - サーバ実行ポートは `WEB_PORT` 環境変数で変更可能
    - **db**: `mysql:5.7`
        - MySQL Datbase Server
        - tcp://localhost:8033 => service://db:3306
            - MySQL接続ポートは `DB_PORT` 環境変数で変更可能
        - Login:
            - User: `root`
            - Password: `root`
            - Database: `sekokan`
    - **phpmyadmin**: `phpmyadmin/phpmyadmin`
        - MySQL Database Admin Server
        - http://localhost:8057 => service://phpmyadmin:80
            - 管理画面ポートは `PMA_PORT` 環境変数で変更可能
    - **mailhog**: `mailhog/mailhog`
        - SMTP Server + Mail catching sandbox environment
        - http://localhost:8025 => service://mailhog:8025
            - 管理画面ポートは `MAILHOG_PORT` 環境変数で変更可能
            - SMTP接続ポートはポートフォワーディングなし (service://mailhog:1025)

### Setup
```bash
# Dockerコンテナを構築
## $ export USER_ID=$UID && docker-compose build
$ ./lamp build

# Dockerコンテナをバックグラウンドで起動
## $ export USER_ID=$UID && docker-compose up -d
$ ./lamp up -d

# => web: http://localhost:8080
# => phpmyadmin: http://localhost:8057
# => mailhog: http://localhost:8025

# Laravel 8.* プロジェクトを作成
## $ rm -rf ./www/app/
## $ export USER_ID=$UID
## $ docker-compose exec -w '/var/www/' web composer create-project --prefer-dist laravel/laravel app '8.*'
## $ sed -ie '各種設定' ./www/app/.env
$ ./lamp init-laravel-project
```
