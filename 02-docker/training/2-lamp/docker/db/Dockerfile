FROM mysql:5.7

# MySQL設定ファイルをコンテナ内にコピー
COPY ./my.cnf /etc/mysql/conf.d/

# my.cnf が World-writable になってしまっているので、パーミッションを 644 に設定
RUN chmod 644 /etc/mysql/my.cnf

# 3306番ポートを開ける
## mysql:5.7 はデフォルトで開いているが念の為
EXPOSE 3306
