<?php
/**
 * Garden\Cli\Cli による引数解析
 * $ php tests/cli.php -?    # help
 * $ php tests/cli.php backup --bucket <バケット名> [options] <バックアップ対象> [バックアップ先]
 */

require_once(__DIR__ . '/../lib/Cli/Cli.php');

use Garden\Cli\Cli;

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
var_dump($args);
var_dump($args->getOpt('bucket', ''));
var_dump($args->getArgs());
