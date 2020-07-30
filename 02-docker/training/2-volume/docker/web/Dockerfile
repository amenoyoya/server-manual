FROM php:7.4-apache

RUN : 'mbstring の代わりに鬼車を導入（PHP 7.4～）, ついでに vim も入れておく' && \
    apt-get update && apt-get install -y libonig-dev vim && \
    : '必要なPHPエクステンションを導入' && \
    docker-php-ext-install pdo pdo_mysql && \
    : 'apt-getキャッシュ削除' && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# 80番ポートを開ける
## php:7.4-apache はデフォルトで開いているが念の為
EXPOSE 80
