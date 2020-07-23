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

# -- VirtualHost settings --
# VirtualHostに関する設定ファイルをコンテナ内にコピー
COPY ./apache2/sites-available/vhost_test1.conf /etc/apache2/sites-available/
COPY ./apache2/sites-available/vhost_test2.conf /etc/apache2/sites-available/
# VirtualHost有効化
## /etc/apache2/sites-available/ 内にあるconfファイルの名前で有効化できる
RUN a2ensite vhost_test1
RUN a2ensite vhost_test2
# -- /end VirtualHost settings --

# ※Apacheは、COPYやRUNの後に起動するようなので再起動は不要
# Apache再起動
#RUN service apache2 restart