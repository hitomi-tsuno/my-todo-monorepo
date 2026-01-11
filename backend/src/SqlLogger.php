<?php
// backend/src/SqlLogger.php

require_once __DIR__ . '/Logger.php';

class SqlLogger extends Logger
{
    private string $file;

    public function __construct()
    {
        parent::__construct();
        $this->file = $this->logDir . '/sql.log';
    }

    public function log(string $sql, array $params = []): void
    {
        $line = $this->formatLine('SQL', $sql, $params);
        file_put_contents($this->file, $line, FILE_APPEND);
    }
}
