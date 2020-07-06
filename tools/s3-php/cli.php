<?php
/**
 * s3-php cli
 * @required php-mbstring php-curl php-xml
 * 
 * Copyright (c) 2020, amenoyoya. All rights reserved.
 * https://github.com/amenoyoya/
 * 
 * $ php cli.php --help
 * $ php cli.php <command> [<options>] [<args>]
 */

require_once(__DIR__ . '/lib/Cli/Cli.php');
require_once(__DIR__ . '/lib/S3Client.php');

use Garden\Cli\Cli;

/**
 * S3Cli class
 */
class S3Cli extends S3Client
{
    private $bucket = '';

    public function __construct($args)
    {
        parent::__construct($args->getOpt('keyid', null), $args->getOpt('secret', null), $args->getOpt('region', null));
        if (empty($this->bucket = $args->getOpt('bucket', ''))) {
            throw new Exception('S3バケットが指定されていません');
        }
    }

    /**
     * バケット内ファイル一覧表示
     */
    public function list()
    {
        foreach ($this->getBucket($this->bucket) as $data) {
            if (mb_substr($data['name'], -1) === '/') {
                // ディレクトリは無視
                continue;
            }
            printf("%s: %s %s\n", $data['name'], $this->byteformat($data['size']), date('Y-m-d H:i:s', $data['time']));
        }
    }

    /**
     * S3アップロード
     * @param string $src 対象ファイル
     * @param string $dest アップロード先 S3 ファイル
     * @param string $acl 'public' | 'private'
     */
    public function upload($src, $dest, $acl = 'private')
    {
        if (!$this->putObjectFile($src, $this->bucket, $dest, $acl === 'public'? self::ACL_PUBLIC: self::ACL_PRIVATE)) {
            echo "failed to upload: '{$src}' => '{$dest}'\n";
        }
    }

    /**
     * S3ダウンロード
     * @param string $src 対象 S3 ファイル
     * @param string $dest ダウンロード先ファイル
     */
    public function download($src, $dest)
    {
        if (!$this->getObject($this->bucket, $src, $dest)) {
            echo "failed to download: '{$src}' => '{$dest}'\n";
        }
    }

    /**
     * S3ファイル削除
     * @param string $target 対象 S3 ファイル
     */
    public function remove($target)
    {
        if (!$this->deleteObject($this->bucket, $target)) {
            echo "failed to remove: '{$src}'\n";
        }
    }

    /**
     * S3にファイル・ディレクトリをバックアップ（世代管理）
     * @param string $src バックアップ対象
     * @param string $dest バックアップ先 S3 パス
     * @param int $generations バックアップ保持世代数
     */
    public function backup($src, $dest = '', $generations = null)
    {
        if (is_file($src)) {
            if ($this->backupFile($src, $this->bucket, $dest)) {
                echo "uploaded: {$src}\n";
            } else {
                echo "failed to upload: {$src}\n";
            }
            if (is_numeric($generations) && $generations > 0) {
                $this->rotateFileBackup($src, $generations, $this->bucket, $dest);
            }
        }
        if (is_dir($src)) {
            $this->backupDirectory($src, $this->bucket, $dest);
            if (is_numeric($generations) && $generations > 0) {
                $this->rotateDirectoryBackup($src, $generations, $this->bucket, $dest);
            }
        }
    }
    
    /**
     * バイト数を見やすいフォーマットに変換
     * @param int $bytes
     * @return string
     */
    private function byteformat($bytes)
    {
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
}

$cli = Cli::create()
    ->command('list')
        ->description('S3バケット内ファイルを一覧表示')
    ->command('upload')
        ->description('ファイルをS3にアップロード')
        ->opt('acl:a', 'アクセスコントロール [public | private (default)]', false)
        ->arg('src', '対象ファイル', true)
        ->arg('dest', 'アップロード先 S3 ファイル', true)
    ->command('download')
        ->description('ファイルをS3からダウンロード')
        ->arg('src', '対象 S3 ファイル', true)
        ->arg('dest', 'ダウンロード先ファイル', true)
    ->command('remove')
        ->description('S3からファイルを削除')
        ->arg('target', '削除対象 S3 ファイル名', true)
    ->command('backup')
        ->description('S3にファイル, ディレクトリをバックアップ')
        ->opt('generations:g', '世代管理数: 指定世代数になるように古いバックアップを削除 (指定しない場合は削除しない)', false)
        ->arg('src', 'バックアップ対象', true)
        ->arg('dest', 'バックアップ先 S3 パス', false)
    ->command('*')
        ->opt('bucket:b', 'バックアップ先の S3 バケット', true)
        ->opt('keyid:k', 'AWS_ACCESS_KEY_ID: 指定しない場合は環境変数か ~/.aws/credentials から取得', false)
        ->opt('secret:s', 'AWS_SECRET_ACCESS_KEY: 指定しない場合は環境変数か ~/.aws/credentials から取得', false)
        ->opt('region:r', 'AWS_S3_REGION: 指定しない場合は環境変数から取得 (default: "ap-northeast-1")', false);

// Parse and return cli args.
$args = $cli->parse($argv);
$targets = $args->getArgs();

// S3 Client
$client = new S3Cli($args);
switch ($args->getCommand()) {
    case 'list':
        $client->list();
        break;
    case 'upload':
        if (!isset($targets['src']) || !isset($targets['dest'])) {
            throw new Exception('対象ファイルとアップロード先を指定してください');
        }
        $client->upload($targets['src'], $targets['dest'], $args->getOpt('acl', 'private'));
        break;
    case 'download':
        if (!isset($targets['src']) || !isset($targets['dest'])) {
            throw new Exception('対象ファイルとダウンロード先を指定してください');
        }
        $client->download($targets['src'], $targets['dest']);
        break;
    case 'remove':
        if (!isset($targets['target'])) {
            throw new Exception('対象ファイルを指定してください');
        }
        $client->remove($targets['target']);
        break;
    case 'backup':
        if (!isset($targets['src'])) {
            throw new Exception('バックアップ対象を指定してください');
        }
        $client->backup($targets['src'], isset($targets['dest'])? $targets['dest']: '', $args->getOpt('generations', null));
        break;
}
