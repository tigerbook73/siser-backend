<?php

namespace App\Services\Lds;

use Illuminate\Support\Facades\Log;

class LdsLog
{
  public static function log(string $level, string $action, string $result, string $text, array $context = []): void
  {
    Log::Log($level, "LDS_LOG: $action $result: $text", $context);
  }

  public static function info(string $action, string $result, string $text, array $context = []): void
  {
    self::log('info', $action, $result, $text, $context);
  }

  public static function warning(string $action, string $result, string $text, array $context = []): void
  {
    self::log('warning', $action, $result, $text, $context);
  }

  public static function error(string $action, string $result, string $text, array $context = []): void
  {
    self::log('error', $action, $result, $text, $context);
  }
}
