# Docker Tips

## 作業済みコンテナのイメージ化

Dockerコンテナ内で設定等の作業を行った後、そのコンテナごとDockerイメージにパッケージングする方法

### Dockerコンテナからイメージ作成
- まずコンテナを終了する
    ```bash
    $ docker-compose stop

    # コンテナが終了していることを確認
    $ docker-compose ps
        Name                  Command               State    Ports
    --------------------------------------------------------------
    training-php   docker-php-entrypoint apac ...   Exit 0
    ```
- 停止中のコンテナからイメージを作成
    ```bash
    # 以下のコマンドでイメージを作成できる
    ## docker commit <コンテナ名> <イメージ名>[:[タグ名]]
    ## タグ名は省略すると latest になる
    $ docker commit training-php php_composer-apache

    # ローカルに保存されているdockerイメージを確認
    $ docker images
    REPOSITORY                 TAG                 IMAGE ID            CREATED             SIZE
    php_composer-apache        latest              d69f78ac08d3        19 seconds ago      473MB
    training_php               latest              8deb76970c3c        2 hours ago         449MB
    ```

### Dockerイメージのエクスポート
- ローカルに保存されているDockerイメージをファイルに保存する
    - Vagrant環境の場合、以下の点に注意する
        1. **保存先ディレクトリには、保存が終わるまでWindows側からは触らないこと**
        2. 巨大ファイルの書き込みをしようとするとエラーになるため、**ファイル分割しながら保存すること**
    ```bash
    # Windowsとの共有ディレクトリ内にイメージ保存用ディレクトリを作成
    ## 必ずCentOS側からmkdirし、Windows側からは絶対に操作しないこと
    ## ※ Windows側で操作したディレクトリには、tar保存できなくなる現象が発生する
    $ mkdir -p docker-image

    $ cd docker-image

    # 以下のコマンドでイメージをtarファイルに保存できる
    ## docker save <イメージ名(リポジトリ名)> > <保存先ファイル>
    # 以下のコマンドでファイル分割できる
    ## split -b <分割サイズ> -a <ファイル接尾語の桁数(デフォルト: 2桁)> <入力ファイル> <出力ファイル>
    # 色々試したところ、100MBに分割すれば保存できたため、以下のように保存する
    $ docker save php_composer-apache | split -b 100M - php_composer-apache.tar_

    # => php_composer-apache.tar_aa, php_composer-apache.tar_ab, ... が出力される
    ```
- 分割保存したイメージファイルをWindows側で連結する
    - `docker-image`フォルダでコマンドプロンプトを起動
        ```bash
        > copy /b php_composer-apache.tar_* php_composer-apache.tar
        ```

### Dockerイメージのインポート
- tar保存したDockerイメージをインポートする
    ```bash
    $ docker load < php_composer-apache.tar
    Loaded image: php_composer-apache:latest
    ```
- ローカルに保存されているDockerイメージを使って、DockerComposeでコンテナをビルドする
    - `docker-compose.yml`の`image`でイメージ名(リポジトリ名)を指定するだけ
        ```yaml
        version: '2'
        services:
        php:
            image: php_composer-apache # インポートしたDockerイメージを指定
            ports:
            - '80:80'
            volumes:
            - ./html:/var/www/html
        ```
    - 通常通り起動すればOK
        ```bash
        $ docker-compose up -d --build
        ```
