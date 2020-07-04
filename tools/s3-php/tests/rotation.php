<?php
/**
 * 指定 S3 バケット内ファイルを世代管理
 * $ php tests/enumerate.php <bucket_name>
 */

require_once(__DIR__ . '/../lib/S3Client.php');
$client = new S3Client();
$bucket = @$argv[1];

if (!$bucket) {
    die("バケットを指定してください\n");
}

// この上位ディレクトリを S3 バックアップ世代管理
// 最新の1世代のみ残し、他のバックアップを削除
var_dump($client->rotateDirectoryBackup(dirname(__DIR__), 1, $bucket));
