<?php
// backend/src/ErrorLogger.php

require_once __DIR__ . '/Logger.php';

class ErrorLogger extends Logger
{
    private string $file;

    public function __construct()
    {
        parent::__construct();
        $this->file = $this->logDir . '/error.log';
    }

    public function log(string $message, array $context = []): void
    {
        $line = $this->formatLine('ERROR', $message, $context);
        file_put_contents($this->file, $line, FILE_APPEND);
    }
}
