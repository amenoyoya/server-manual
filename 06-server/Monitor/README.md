# サービス死活監視

## Environment

- Docker: `19.03.12`
    - docker-compose: `1.26.0`

### Structure
```bash
./
|_ docker/
|  |_ amazonlinux2/ # amazonlinux2 service container
|  |  |_ Dockerfile
|  |
|  |_ centos6/ # centos6 service container
|     |_ Dockerfile
|
|_ docker-compose.yml # docker containers building file
```

### Docker
- services:
    - **amazonlinux2**: `amazonlinux:2`
        - amazonlinux2: php:7.3 + apache:2.4 web server
        - http://localhost:18073 => service://amazonlinux2:80
        - ssh://localhost:12273 => service://amazonlinux2:22
    - **centos6**: `centos:6`
        - centos6: php:7.3 + apache:2.4 web server
        - http://localhost:28073 => service://amazonlinux2:80
        - ssh://localhost:22273 => service://amazonlinux2:22

### Setup
```bash
# Dockerコンテナ構築
$ docker-compose build

# Dockerコンテナ起動
$ docker-compose up -d
```

***

## AWS S3 へのバックアップ

### AWS S3 バケットポリシー設定
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Principal": {
                "AWS": "arn:aws:iam::{iam_user_id}:user/{iam_user_name}"
            },
            "Action": "s3:*",
            "Resource": [
                "arn:aws:s3:::{bucket_name}",
                "arn:aws:s3:::{bucket_name}/*"
            ]
        }
    ]
}
```

パブリックアクセスは全てブロックで良い

### AWS CLI インストール
```bash
$ curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
$ unzip awscliv2.zip
$ sudo ./aws/install

# version確認
$ /usr/local/bin/aws --version
aws-cli/2.1.26 Python/3.7.3 Linux/3.10.0-1062.18.1.el7.x86_64 exe/x86_64.centos.7 prompt/off
```

***

## ログ監視サービス実装 (CentOS:7 以上)

[CentOS7系と6系のコマンドの違いについて](https://qiita.com/shotaTsuge/items/9f337bad9f73c3953af1)

### httpdサービス死活監視

httpd (apache) サーバの死活監視を行い、自動的に復旧する

少し変更すれば httpd だけでなく、nginx や mysqld にも応用可能である

#### /opt/monit/httpd.sh
```bash
# journalctl でログ監視し、ログが更新されたタイミングで発火
journalctl -f | while read line; do
    # httpd が running 状態か確認
    STAT=`systemctl status httpd | grep running`
    if [ "$STAT" = ""  ]; then
        # ゾンビ化した httpd プロセスがあるかもしれないため念の為 kill
        # kill -9 `ps auxw | grep httpd | awk '{print $2}'`
        # httpd 再起動
        systemctl start httpd
        # ログ記録
        echo "$(date) | httpd start automatically" | tee -a /opt/monit/httpd.log
    fi
done
```
