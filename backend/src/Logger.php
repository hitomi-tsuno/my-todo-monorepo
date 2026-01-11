<?php
// backend/src/Logger.php

abstract class Logger
{
    protected string $logDir;

    public function __construct()
    {
        $this->logDir = getenv('LOG_DIR') ?: __DIR__ . '/../log';

        // ディレクトリがなければ作成
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }
    }

    /**
     * ログを書き込む抽象メソッド
     */
    abstract public function log(string $message, array $context = []): void;

    /**
     * 共通：ログ行を整形
     */
    protected function formatLine(string $level, string $message, array $context = []): string
    {
        $time = date('Y-m-d H:i:s');
        $json = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';

        return "[$time][$level] $message $json" . PHP_EOL;
    }
}
