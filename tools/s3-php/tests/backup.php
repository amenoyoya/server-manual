<?php
/**
 * 指定 S3 バケットにディレクトリバックアップ
 * $ php tests/enumerate.php <bucket_name>
 */

require_once(__DIR__ . '/../lib/S3Client.php');
$client = new S3Client();
$bucket = @$argv[1];

if (!$bucket) {
    die("バケットを指定してください\n");
}

// この上位ディレクトリを S3 にバックアップ
var_dump($client->backupDirectory(dirname(__DIR__), $bucket));
