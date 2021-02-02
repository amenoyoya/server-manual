#!/bin/bash

cd $(dirname $0)

# AWS用環境変数
export AWS_ACCESS_KEY_ID=minio
export AWS_SECRET_ACCESS_KEY=minio123
export AWS_DEFAULT_REGION=ap-northeast-1
export AWS_ENDPOINT_URL=http://minio:9000 # 通常のAWSを使う場合は指定不要

# サイトデータバックアップ
function backup_site() {
    local site_dir="$1"
    if [ ! -d ./backup/ ]; then
        mkdir ./backup/
    fi
    tar czvf ./backup/site_data.tar.gz "$site_dir"
}

# DBデータバックアップ
function backup_db() {
    local mysql_host="${1:-localhost}"
    local mysql_port="${2:-3306}"
    local mysql_user="$3"
    local mysql_password="$4"
    local mysql_database="$5"
    if [ ! -d ./backup/ ]; then
        mkdir ./backup/
    fi
    mysqldump -u"$mysql_user" -p"$mysql_password" -h"$mysql_host" -P"$mysql_port" --single-transaction --routines --databases "$mysql_database" > ./backup/db_data.sql
}

# S3アップロード
function upload_backup() {
    local bucket="$1"
    local generations="$2"
    local command=`test "$AWS_ENDPOINT_URL" != "" && echo "aws --endpoint-url=$AWS_ENDPOINT_URL" || echo 'aws'`
    # ./backup/ => s3://{bucket}/backup.{Ymd_HMS}/
    $command s3 cp --recursive ./backup/ "s3://$bucket/backup.$(date '+%Y%m%d_%H%M%S')/"
    # 保持する世代数が指定されている場合は、溢れた分のバックアップを削除
    if [ "$generations" != "" ]; then
        local backup_count=`$command s3 ls s3://$bucket/ | awk '{print $NF}' | grep -Ec '^backup\.[0-9]+_[0-9]+/$'`
        if [ $backup_count -gt $generations ]; then
            # バックアップディレクトリを古い順に並べて配列化
            local backups=(`$command s3 ls s3://$bucket/ | awk '{print $NF}' | grep -E '^backup\.[0-9]+_[0-9]+/$'`)
            # 保持世代数から溢れた分だけS3から削除
            for ((i = 0; i < $backup_count - $generations; ++i)); do
                $command s3 rm --recursive "s3://$bucket/${backups[$i]}"
            done
        fi
    fi
}

# バックアップ実行
backup_site /root/scripts
backup_db db 3306 root root app

# S3アップロード
upload_backup centos6 3