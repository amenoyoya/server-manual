# php7とApacheがパックされたイメージを使う
## php:7-apacheイメージはDebianベースのため、設定はDebian系の設定方法に従う
FROM php:7-apache

# ./php.ini を コンテナの /usr/local/etc/php/ ディレクトリにコピー
COPY ./php.ini /usr/local/etc/php/

# -- mod_rewrite settings --
# mod_rewrite有効化
RUN a2enmod rewrite
# ポート設定ファイルをコンテナ内にコピー
COPY ./apache2/ports.conf /etc/apache2/
# デフォルトサイトの設定（ルートディレクトリのアクセス権等）を使いやすく
COPY ./apache2/sites-available/000-default.conf /etc/apache2/sites-available/
# -- /end mod_rewrite settings --

# -- Composer settings --
# php で必要なものを入れておく
RUN apt-get update && apt-get install -y unzip git && \
    docker-php-ext-install pdo_mysql mysqli mbstring
RUN mkdir /var/php -p
WORKDIR /var/php
# composerインストール(2行目はバージョンによってハッシュ値が変わるので作成時の最新のものを入れるべき)
# => https://composer.github.io/pubkeys.html
RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php ;\
php -r "if (hash_file('SHA384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" ;\
php composer-setup.php --filename=composer;\
php -r "unlink('composer-setup.php');" ;\
mv composer /usr/local/bin/composer
# Composerをrootユーザーで実行可能にする
ENV COMPOSER_ALLOW_SUPERUSER 1
# Composerのグローバルパッケージのインストール先を変更
ENV COMPOSER_HOME /composer
# ComposerのグローバルパッケージへのPATHを通す
ENV PATH $PATH:/composer/vendor/bin
# -- /end Composer settings --

# ※Apacheは、COPYやRUNの後に起動するようなので再起動は不要
# Apache再起動
#RUN service apache2 restart