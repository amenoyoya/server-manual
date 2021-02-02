<?php
/**
 * AWS S3 Client (amazon-s3-php-class wrapper)
 * amazon-s3-php-class: https://github.com/tpyo/amazon-s3-php-class
 * 
 * Copyright (c) 2020, amenoyoya. All rights reserved.
 * https://github.com/amenoyoya/
 * 
 * MIT License
 */

require_once(__DIR__ . '/S3.php');

/**
 * ディレクトリバックアップ時のデフォルトコールバックメソッド
 * @param bool $result 直前のファイルバックアップが成功したか
 * @param string $fileFullPath 直前のバックアップファイル名（フルパス）
 * @param string $subDir 直前のバックアップ先サブディレクトリ名
 */
function S3Client_backupDirectoryCallback($result, $fileFullPath, $subDir) {
    if ($result) {
        echo "uploaded: $fileFullPath\n";
    } else {
        echo "failed to upload: $fileFullPath\n";
    }
}

/**
 * バックアップ世代ローテーション時のデフォルトコールバックメソッド
 * @param bool $result 直前のファイル削除が成功したか
 * @param string $s3filePath 削除された S3 ファイル名
 */
function S3Client_rotateBackupCallback($result, $s3filePath) {
    if ($result) {
        echo "deleted: $s3filePath\n";
    } else {
        echo "failed to delete: $s3filePath\n";
    }
}

class S3Client extends S3 {
    /**
     * コンストラクタ
     * 接続情報: 指定しない場合、環境変数から取得 => 環境変数が設定されていない場合は ~/.aws/credentials から読み込む
     */
    public function __construct($accessKey = null, $secretKey = null, $region = null, $endpoint = null, $ssl = true)
    {
        $settings = [
            'access_key_id' => $accessKey ?: getenv('AWS_ACCESS_KEY_ID'),
            'secret_access_key' => $secretKey ?: getenv('AWS_SECRET_ACCESS_KEY'),
            'region' => $region ?: (getenv('AWS_S3_REGION') ?: 'ap-northeast-1'),
            'ssl' => $ssl,
        ];
        // endpointを指定する場合（MinIO等を使う場合）
        $settings['endpoint'] = $endpoint ?: "s3.{$settings['region']}.amazonaws.com";

        if (!$settings['access_key_id'] || !$settings['secret_access_key']) {
            $inifile = (getenv('HOME')?: '/root') . '/.aws/credentials';
            if (is_file($inifile) && $conf = parse_ini_file($inifile, true)) {
                $settings['access_key_id'] = @$conf['default']['aws_access_key_id'];
                $settings['secret_access_key'] = @$conf['default']['aws_secret_access_key'];
            }
            if (!$settings['access_key_id'] || !$settings['secret_access_key']) {
                throw new Exception('AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY が設定されていません');
            }
        }
        // S3クライアント作成
        parent::__construct(
            $settings['access_key_id'],
            $settings['secret_access_key'],
            $settings['ssl'],
            $settings['endpoint'],
        );
    }

    /** 
     * ファイル名にバックアップ日時を付与して S3 にアップロード
     * @param string $filename バックアップするファイル名
     * @param string $bucket バックアップ先の S3 バケット
     * @param string $destDir バックアップ先のディレクトリ名
     * @return bool s3://$bucket/$destDir/$filename.YYYYmmdd-HHiiss にバックアップされたら true
     */
    public function backupFile($filename, $bucket, $destDir = '')
    {
        $destDir = rtrim($destDir, '/') . '/';
        if ($destDir === '/') {
            $destDir = ''; // ルートディレクトリ
        }
        return $this->putObjectFile(
            $filename,
            $bucket,
            $destDir . basename($filename) . date('.Ymd-His'),
            self::ACL_PRIVATE
        );
    }

    /**
     * S3 にバックアップされたファイルを世代管理
     * 指定した世代数を超えたバックアップは古い順に削除
     * @param string $filename バックアップするファイル名
     * @param int $generations 世代数
     * @param string $bucket バックアップ先の S3 バケット
     * @param string $destDir バックアップ先のディレクトリ名
     * @param function $callback (bool $result, string $fileFullPath, string $subDir) => void
     * @return bool
     */
    public function rotateFileBackup($filename, $geneations, $bucket, $destDir = '', $callback = 'S3Client_rotateBackupCallback')
    {
        $destDir = rtrim($destDir, '/') . '/';
        if ($destDir === '/') {
            $destDir = ''; // ルートディレクトリ
        }
        // S3 バケット内ファイルを全て取得
        $files = $this->getBucket($bucket);
        if (!is_array($files)) {
            return true;
        }
        // 指定ファイルのバックアップファイルパスを取得
        $targets = $this->filterBackupFiles($destDir . basename($filename), $files);
        if (count($targets) <= $geneations) {
            return true;
        }
        // 世代数を超えた分のバックアップファイルを古い順に削除
        $result = true;
        foreach ($this->filterRotationBackupFiles($geneations, $targets) as $data) {
            $res = $this->deleteObject($bucket, $data['name']);
            if (!$res) {
                $result = false;
            }
            if (is_callable($callback)) {
                $callback($res, $data['name']);
            }
        }
        return $result;
    }

    /**
     * ディレクトリ丸ごと S3 バックアップ
     * @param string $dirname バックアップ対象ディレクトリ
     * @param string $bucket バックアップ先の S3 バケット
     * @param string $destDir バックアップ先のディレクトリ名
     * @param function $callback (bool $result, string $fileFullPath, string $subDir) => void
     * @return bool s3://$bucket/$destDir/$dirname.eachFile.YYYYmmdd-HHiiss に全てバックアップされたら true
     */
    public function backupDirectory($dirname, $bucket, $destDir = '', $callback = 'S3Client_backupDirectoryCallback')
    {
        $destDir = rtrim($destDir, '/') . '/';
        if ($destDir === '/') {
            $destDir = ''; // ルートディレクトリ
        }
        return $this->processFilesInDirectory(
            $dirname,
            function ($fileFullPath, $subDir) use($bucket, $destDir, $callback) {
                $result = $this->backupFile($fileFullPath, $bucket, $destDir . $subDir);
                if (is_callable($callback)) {
                    $callback($result, $fileFullPath, $subDir);
                }
                return $result;
            }
        );
    }

    /**
     * S3 にバックアップされたディレクトリを世代管理
     * 指定した世代数を超えたバックアップは古い順に削除
     * @param string $dirname バックアップ対象ディレクトリ
     * @param int $geneations 世代数
     * @param string $bucket バックアップ先の S3 バケット
     * @param string $destDir バックアップ先のディレクトリ名
     * @param function $callback (bool $result, string $s3filePath) => void
     * @return bool
     */
    public function rotateDirectoryBackup($dirname, $geneations, $bucket, $destDir = '', $callback = 'S3Client_rotateBackupCallback')
    {
        $destDir = rtrim($destDir, '/') . '/';
        if ($destDir === '/') {
            $destDir = ''; // ルートディレクトリ
        }
        // S3 バケット内ファイルを全て取得
        $files = $this->getBucket($bucket);
        if (!is_array($files)) {
            return true;
        }
        // ディレクトリ走査
        return $this->processFilesInDirectory(
            $dirname,
            function ($fileFullPath, $subDir) use($files, $geneations, $bucket, $destDir, $callback) {
                // 指定ファイルのバックアップファイルパスを取得
                $targets = $this->filterBackupFiles($destDir . $subDir . basename($fileFullPath), $files);
                if (count($targets) <= $geneations) {
                    return true;
                }
                // 世代数を超えた分のバックアップファイルを古い順に削除
                $result = true;
                foreach ($this->filterRotationBackupFiles($geneations, $targets) as $data) {
                    $res = $this->deleteObject($bucket, $data['name']);
                    if (!$res) {
                        $result = false;
                    }
                    if (is_callable($callback)) {
                        $callback($res, $data['name']);
                    }
                }
                return $result;
            }
        );
    }

    /**
     * ディレクトリ走査メソッド
     * @param string $dirname 対象ディレクトリ名
     * @param function $calbback (string $fileFullPath, string $subDir) => bool
     * @param string $baseDir サブディレクトリ名算出用
     */
    protected function processFilesInDirectory($dirname, $callback, $baseDir = null)
    {
        if (!is_dir($dirname) || !is_readable($dirname)) {
            return false;
        }
        $dirname = rtrim(realpath($dirname), '/') . '/';
        if (!$baseDir) {
            $baseDir = $dirname;
        }
        // サブディレクトリ名: $dirname と $baseDir の差分
        $subDir = mb_substr($dirname, mb_strlen($baseDir));
        // ディレクトリ走査
        if ($dh = opendir($dirname)) {
            $result = true;
            while (($filename = readdir($dh)) !== false) {
                if ($filename === '.' || $filename === '..') {
                    continue;
                }
                $filename = $dirname . $filename;
                // ディレクトリなら再帰
                if (is_dir($filename) && !$this->processFilesInDirectory($filename, $callback, $baseDir)) {
                    $result = false;
                }
                // ファイルなら callback 実行
                if (is_file($filename) && is_callable($callback) && !$callback($filename, $subDir)) {
                    $result = false;
                }
            }
            return $result;
        }
        return false;
    }

    /**
     * S3 上にあるファイル一覧から指定ファイルのバックアップと思われるファイルを抽出
     * @param string $filepath
     * @param array<S3File> $s3files
     *      S3File ['name' => ファイル名, 'time' => 更新日時, 'size' => ファイルサイズ, 'hash' => ハッシュ]
     * @return array<S3File>
     */
    private function filterBackupFiles($filepath, $s3files)
    {
        return array_filter($s3files, function($data) use($filepath) {
            // バックアップファイル: $filepath.YYYYmmdd-HHiiss
            if (preg_match('/^' . preg_quote($filepath, '/') . '\.[0-9]{8}\-[0-9]{6}$/', $data['name']) === 1) {
                return true;
            }
            return false;
        });
    }

    /**
     * 指定世代数を超えた分のバックアップファイルを古い順に取得
     * @param int $generations
     * @param array<S3File> $filteredS3files
     *      S3File ['name' => ファイル名, 'time' => 更新日時, 'size' => ファイルサイズ, 'hash' => ハッシュ]
     * @return array<S3File>
     */
    private function filterRotationBackupFiles($geneations, $filteredS3files)
    {
        // ['time'] 昇順でソート
        if(!array_multisort(array_column($filteredS3files, 'time'), SORT_ASC, $filteredS3files)) {
            return [];
        }
        // 世代数を超えた分だけ取得
        return array_slice($filteredS3files, 0, count($filteredS3files) - $geneations);
    }
}
