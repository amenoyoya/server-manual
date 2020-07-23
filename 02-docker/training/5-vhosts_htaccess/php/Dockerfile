# php7とApacheがパックされたイメージを使う
## php:7-apacheイメージはDebianベースのため、設定はDebian系の設定方法に従う
FROM php:7-apache

# ./php.ini を コンテナの /usr/local/etc/php/ ディレクトリにコピー
COPY ./php.ini /usr/local/etc/php/

# -- mod_rewrite settings --
# mod_rewrite有効化
RUN a2enmod rewrite
# mod_proxy_http有効化（htaccessで[P]フラグを使うために必須）
RUN a2enmod proxy_http
# ポート設定ファイルをコンテナ内にコピー
COPY ./apache2/ports.conf /etc/apache2/
# デフォルトサイトの設定（ルートディレクトリのアクセス権等）を使いやすく
COPY ./apache2/sites-available/000-default.conf /etc/apache2/sites-available/
# -- /end mod_rewrite settings --

# ※Apacheは、COPYやRUNの後に起動するようなので再起動は不要
# Apache再起動
#RUN service apache2 restart