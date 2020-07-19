# s3-php

AWS CLI が使えないレンタルサーバでも使える PHP 製の AWS S3 操作 CLI

AWS CLI だけでは難しい世代管理式のバックアップ処理も実行可能

## Environment

- OS: Linux 系 OS
- PHP: 7系
- AWS: S3 へのアクセス権限のあるアクセスキー、シークレットアクセスキーを発行しておく

### Setup
AWSアクセスキー、シークレットアクセスキーを s3-php 用に設定する

設定方法は、参照優先順位の高い順に、以下の3つの方法がある

1. `cli.php` のコマンドライン引数で渡す
    - `--keyid`, `-k`: AWSアクセスキーを設定
    - `--secret`, `-s`: AWSシークレットアクセスキーを設定
2. 環境変数で設定する
    - `AWS_ACCESS_KEY_ID`: AWSアクセスキーを設定
    - `AWS_SECRET_ACCESS_KEY`: AWSシークレットアクセスキーを設定
3. AWS CLI 標準の設定ファイルで設定する
    - `~/.aws/credentials` の `default` プロファイルで設定

さらに、AWS S3 バケットのリージョンも設定する

設定方法は、参照優先順位の高い順に、以下の2つの方法がある

1. `cli.php` のコマンドライン引数で渡す
    - `--region`, `-r`: リージョンを設定
2. 環境変数で設定する
    - `AWS_S3_REGION`: リージョンを設定

なお設定しない場合、リージョンは `ap-northeast-1` が設定される

***

## Usage

```bash
# Usage
$ php cli.php <command> [<options>] [<args>]

# Help
$ php cli.php --help
```

### 全コマンド用コマンドラインオプション
- `--bucket <bucket_name>`, `b <bucket_name>`:
    - S3 バケットを設定（**必須**）
- `--keyid <key_id>`, `-k <key_id>`:
    - AWS_ACCESS_KEY_ID: 指定しない場合は環境変数か `~/.aws/credentials` から取得
- `--secret <secret_key>`, `-s <secret_key>`:
    - AWS_SECRET_ACCESS_KEY: 指定しない場合は環境変数か `~/.aws/credentials` から取得
- `--region <region>`, `-r <region>`:
    - AWS_S3_REGION: 指定しない場合は環境変数から取得 (default: `ap-northeast-1`)

### S3バケット参照コマンド
対象の S3 バケット内の全ファイルを列挙する

```bash
# Usage
$ php cli.php list <--bucket, -b: 対象S3バケット名>

# 例: s3://mybucket の全ファイルを列挙
## AWS_ACCESS_KEY_ID: AKIAI44QH8DHBEXAMPLE
## AWS_SECRET_ACCESS_KEY: je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY
## AWS_S3_REGION: ap-northeast-1
$ php cli.php list \
    -k AKIAI44QH8DHBEXAMPLE \
    -s je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY \
    -r ap-northeast-1 \
    -b mybucket
```

### S3アップロードコマンド
対象ファイルを S3 にアップロードする（ディレクトリはアップロードできない）

```bash
# Usage
$ php cli.php upload \
    <--bucket, -b: 対象S3バケット名> \
    [--acl, -a: アクセスコントロール ('public' | 'private' (default))] \
    <アップロード対象ファイル> <アップロード先S3パス>

# 例: ./test.txt を s3://mybucket/dest/test.txt にアップロード（公開する）
## AWS_ACCESS_KEY_ID: AKIAI44QH8DHBEXAMPLE
## AWS_SECRET_ACCESS_KEY: je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY
## AWS_S3_REGION: ap-northeast-1
$ php cli.php upload \
    -k AKIAI44QH8DHBEXAMPLE \
    -s je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY \
    -r ap-northeast-1 \
    -b mybucket -a public \
    ./test.txt dest/test.txt
```

### S3ダウンロードコマンド
対象の S3 ファイルをローカルにダウンロードする（ディレクトリはダウンロードできない）

```bash
# Usage
$ php cli.php download \
    <--bucket, -b: 対象S3バケット名> \
    <ダウンロード対象S3パス> <ダウンロード先パス>

# 例: s3://mybucket/src/test.txt を ./test.txt にダウンロード
## AWS_ACCESS_KEY_ID: AKIAI44QH8DHBEXAMPLE
## AWS_SECRET_ACCESS_KEY: je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY
## AWS_S3_REGION: ap-northeast-1
$ php cli.php upload \
    -k AKIAI44QH8DHBEXAMPLE \
    -s je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY \
    -r ap-northeast-1 \
    -b mybucket \
    src/test.txt ./test.txt
```

### S3ファイル削除コマンド
対象 S3 ファイルを削除する（ディレクトリは削除できない）

```bash
# Usage
$ php cli.php remove <--bucket, -b: 対象S3バケット名> <削除対象ファイル>

# 例: s3://mybucket/src/test.txt を削除
## AWS_ACCESS_KEY_ID: AKIAI44QH8DHBEXAMPLE
## AWS_SECRET_ACCESS_KEY: je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY
## AWS_S3_REGION: ap-northeast-1
$ php cli.php remove \
    -k AKIAI44QH8DHBEXAMPLE \
    -s je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY \
    -r ap-northeast-1 \
    -b mybucket \
    src/test.txt
```

### S3バックアップコマンド
対象のファイル・ディレクトリを丸ごと S3 にバックアップする

また、世代管理を行い、特定の世代以前のバックアップを削除することも可能

おそらく最もよく使うことになるコマンド

```bash
# Usage
$ php cli.php backup \
    <--bucket, -b: 対象S3バケット名> \
    [--generations, -g: 指定世代数になるように古いバックアップを削除 (指定しない場合は削除しない)]
    <バックアップ対象ファイル・ディレクトリ> [バックアップ先S3ディレクトリ]

# 例: ./backup/* を s3://mybucket/dest/backup/ にバックアップ
## AWS_ACCESS_KEY_ID: AKIAI44QH8DHBEXAMPLE
## AWS_SECRET_ACCESS_KEY: je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY
## AWS_S3_REGION: ap-northeast-1
$ php cli.php backup \
    -k AKIAI44QH8DHBEXAMPLE \
    -s je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY \
    -r ap-northeast-1 \
    -b mybucket \
    ./backup/ dest/backup/

## => s3://mybucket/dest/backup/<filename>.<年月日-時分秒> にバックアップされる


# 例2: ./backup/* を s3://mybucket/ に3世代分バックアップ
$ php cli.php backup -b mybucket -g 3 ./backup/

## => s3://mybucket/<filename>.<年月日-時分秒> が3世代分だけバックアップされる
## ※ 3世代を超えた場合は古い順に削除される
```
