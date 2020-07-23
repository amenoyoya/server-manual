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

# -- mod_ssl settings --
COPY ./ssl/server.key /etc/ssl/private/
COPY ./ssl/server.crt /etc/ssl/private/
COPY ./ssl/server.csr /etc/ssl/private/
COPY ./apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/
RUN a2enmod ssl
RUN a2ensite default-ssl
# -- /end mod_ssl settings --

# ※Apacheは、COPYやRUNの後に起動するようなので再起動は不要
# Apache再起動
#RUN service apache2 restart