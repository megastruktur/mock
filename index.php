<?php

use JetBrains\PhpStorm\NoReturn;

require './src/MockProcessor.php';

start();

/**
 * Helper function to echo the content.
 *
 * @param int $code
 * @param string $body
 * @param array $headers
 * @return void
 */
#[NoReturn]
function return_response(int $code, string $body = '', array $headers = []): void {
  http_response_code($code);
  foreach ($headers as $key => $value) {
    header($key . ': ' . $value);
  }

  echo $body;
  die();
}

/**
 * Starter.
 *
 * @return void
 */
function start(): void {
  $MockProcessor = new MockProcessor($_SERVER['REQUEST_METHOD'], $_SERVER['SCRIPT_NAME'], $_REQUEST, getallheaders());
  $MockProcessor->output();
}