<?php // index.php
require_once('/composer/vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

$logging_path = 'log/error.log';
$log = new Logger('test');

// フォーマットに、自作フォーマット `file` と `line` を追加
$output = "[%datetime%] %level_name% %message% %file% %line%\n";

$formatter = new LineFormatter($output);
$stream = new StreamHandler($logging_path, Logger::DEBUG);
$stream->setFormatter($formatter);
$log->pushHandler($stream);

// Processorで自作フォーマットを登録
$log->pushProcessor(function ($record) {
    $record['file'] = $record['context']['file'];
    $record['line'] = $record['context']['line'];
    return $record;
});

function debug($message, $depth=''){
    global $log;
    
    // 呼び出し元ファイルと行数
    $backtrace = debug_backtrace();
    // 指定の深さが存在しない場合は呼び出し元に
    $key = isset($backtrace[$depth]) ? $depth : 0;
    $file = $backtrace[$key]['file'];
    $line = $backtrace[$key]['line'];
    $context = array('file' => $file, 'line' => $line);
    // エラーレベルは一旦固定
    $log->addInfo($message, $context);
}

// ログ出力
debug('debug message!!');

echo 'Monolog test';