<?php
namespace MayaTracking;

class Logger
{
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';

    private $logFile;
    private $logLevel;

    public function __construct($logFile = null, $logLevel = self::INFO)
    {
        $this->logFile = $logFile ?: ABSPATH . 'wp-content/uploads/maya-tracking.log';
        $this->logLevel = $logLevel;
    }

    public function log($message, $level = self::INFO, $context = [])
    {
        if ($this->shouldLog($level)) {
            $logEntry = $this->formatLogEntry($message, $level, $context);
            
            // Log to WordPress error log
            error_log($logEntry);

            // Log to file
            $this->logToFile($logEntry);

            // If it's an error, you might want to display an admin notice
            if ($level === self::ERROR) {
                add_action('admin_notices', function() use ($message) {
                    echo '<div class="error"><p>Maya Tracking Error: ' . esc_html($message) . '</p></div>';
                });
            }
        }
    }

    private function shouldLog($level)
    {
        $levels = [self::DEBUG, self::INFO, self::WARNING, self::ERROR];
        return array_search($level, $levels) >= array_search($this->logLevel, $levels);
    }

    private function formatLogEntry($message, $level, $context)
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? json_encode($context) : '';
        return "[$timestamp] [$level] $message $contextString";
    }

    private function logToFile($logEntry)
    {
        file_put_contents($this->logFile, $logEntry . PHP_EOL, FILE_APPEND);
    }

    public function error($message, $context = [])
    {
        $this->log($message, self::ERROR, $context);
    }

    public function warning($message, $context = [])
    {
        $this->log($message, self::WARNING, $context);
    }

    public function info($message, $context = [])
    {
        $this->log($message, self::INFO, $context);
    }

    public function debug($message, $context = [])
    {
        $this->log($message, self::DEBUG, $context);
    }
}