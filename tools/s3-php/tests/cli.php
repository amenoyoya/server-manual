<?php
/**
 * Garden\Cli\Cli による引数解析
 * $ php tests/cli.php -?    # help
 * $ php tests/cli.php --bucket <バケット名> [options] <バックアップ対象> [バックアップ先]
 */

require_once(__DIR__ . '/../lib/Cli/Cli.php');

use Garden\Cli\Cli;

$cli = new Cli();
$cli->description('Backup files to AWS S3.')
    ->opt('keyid:k', 'AWS_ACCESS_KEY_ID: 指定しない場合は環境変数か ~/.aws/credentials から取得', false)
    ->opt('secret:s', 'AWS_SECRET_ACCESS_KEY: 指定しない場合は環境変数か ~/.aws/credentials から取得', false)
    ->opt('region:r', 'AWS_S3_REGION: 指定しない場合は環境変数から取得 (default: "ap-northeast-1")', false)
    ->opt('generations:g', '世代管理数: 指定世代数になるように古いバックアップを削除 (指定しない場合は削除しない)', false)
    ->opt('bucket:b', 'バックアップ先の S3 バケット', true)
    ->arg('src', 'バックアップ対象', true)
    ->arg('dest', 'バックアップ先 S3 パス', false);

// Parse and return cli args.
var_dump($cli->parse($argv, true));
