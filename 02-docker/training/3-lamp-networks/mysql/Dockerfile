FROM mysql:5.7

# MySQLサーバーの設定ファイルをコンテナ内にコピー
COPY ./my.cnf /etc/mysql/conf.d/

# my.cnfがWorld-writableになってしまっているので、権限を弱める
RUN chmod -R 644 /etc/mysql/conf.d/
