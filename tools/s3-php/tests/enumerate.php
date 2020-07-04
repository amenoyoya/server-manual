<?php
/**
 * 指定 S3 バケット内ファイルを列挙
 * $ php tests/enumerate.php <bucket_name>
 */

require(__DIR__ . '/../lib/S3Client.php');
$client = new S3Client();
$bucket = @$argv[1];

if (!$bucket) {
    die("バケットを指定してください\n");
}

/**
 * バイト数を見やすいフォーマットに変換
 * @param int $bytes
 * @return string
 */
function byteformat($bytes) {
    if ($bytes >= 1024 * 1024 * 1024) {
        return sprintf('%.1fGB', $bytes / 1024 / 1024 / 1024);
    }
    if ($bytes >= 1024 * 1024) {
        return sprintf('%.1fMB', $bytes / 1024 / 1024);
    }
    if ($bytes >= 1024) {
        return sprintf('%.1fKB', $bytes / 1024);
    }
    return sprintf('%dB', $bytes);
}

/**
 * バケット内ファイル一覧取得
 */
foreach ($client->getBucket($bucket) as $data) {
    if (mb_substr($data['name'], -1) === '/') {
        // ディレクトリは無視
        continue;
    }
    printf("%s: %s %s\n", $data['name'], byteformat($data['size']), date('Y-m-d H:i:s', $data['time']));
}
