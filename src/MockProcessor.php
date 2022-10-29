<?php

use JetBrains\PhpStorm\NoReturn;

require __DIR__ . '/Mock.php';

class MockProcessor {

  public array $mocks = [];
  public array $request_url_args = [];
  public string $method = '';
  public string $requested_url = '';
  public array $request_args = [];
  public ?Mock $mock = NULL;
  public array $headers = [];

  private string $mocks_path = __DIR__ . '/../dev/mocks.json';
  private string $mocks_path_test = __DIR__ . '/../dev/mocks_test.json';

  public array $output_extensions = [
    'json' => 'application/json',
    'xml' => 'application/xml',
    'default' => 'text/plain',
  ];


  /**
   * @param string $method
   * @param string $requested_url
   *  URL
   * @param array $request_args
   *  Request arguments.
   * @param array $headers
   */
  public function __construct(string $method, string $requested_url, array $request_args = [], array $headers = []) {

    $this->headers = $headers;
    foreach ($this->loadMocksJSON() as $mock) {
      $this->mocks[] = new Mock($mock);
    }

    // Compare positional arguments of $method and $api['method'] cutting out {{VALUE_NAME}} placeholder from
    // $api['api'] and saving it
    // in $api['variables'] array
    $this->requested_url = $requested_url;
    $this->request_url_args = explode('/', $this->requested_url);
    $this->method = $method;
    $this->request_args = $request_args;


    // Find the proper Mock to show.
    foreach ($this->mocks as $mock) {

      // If valid API - process and return.
      if ($this->isValidAPI($mock)) {
        $this->mock = $mock;
        break;
      }
    }
  }


  private function loadMocksJSON():array {
    // Load ../dev/mocks.json file, parse it as a JSON, throw an error if it's not a valid JSON.

    if ($this->headers && isset($this->headers['TEST'])) {
      $mocks_path = $this->mocks_path_test;
    }
    else {
      $mocks_path = $this->mocks_path;
    }
    $mocks_json = file_get_contents($mocks_path);
    $mocks = json_decode($mocks_json, TRUE);
    if (json_last_error() !== JSON_ERROR_NONE) {
      return_response(500, 'Invalid JSON in ' . $this->mocks_path);
    }
    return $mocks;
  }



  /**
   * Check if this is a proper API to call.
   *
   * @param Mock $mock
   * @return bool
   */
  private function isValidAPI(Mock $mock): bool {

    $use_this_api = FALSE;

    // The Method should be the same. If not, return 405
    if ($this->method === $mock->method) {

      $api_args = explode('/', $mock->api);

      // Compare api and requested_url. Use to speed up the search of APIs without placeholders.
      if ($this->requested_url === $mock->api) {
        $use_this_api = TRUE;
      }
      // Count the arguments so that we can find the right API. Use this one if found placeholder values.
      elseif (count($this->request_url_args) === count($api_args)) {
        // Loop through the arguments, compare but exclude placeholders from comparison
        foreach ($api_args as $key => $api_arg) {
          $use_this_api = ($api_arg === $this->request_url_args[$key] || str_contains($api_arg, '{{'));
          if (!$use_this_api) {
            return FALSE;
          }
        }
      }
    }
    return $use_this_api;
  }


  /**
   * Output the content as echo with proper headers.
   *
   * @return void
   */
  #[NoReturn]
  public function output(): void {

    if ($this->mock) {
      $this->parsePlaceholderVariables();
      // Get the placeholder variables if any and add them to API.
      if ($this->mock->variables) {
        $this->mock->variables = array_merge($this->mock->variables, $this->mock->variables);
      }

      // Get File content with overridden variables.
      $content = $this->mock->getContent();

      $headers = [
        'Content-Type' => $this->getContentType(),
      ];

      return_response(200, $content, $headers);
    }
    else {
      return_response(404, 'Not found');
    }
  }


  /**
   * Get the appropriate Content-Type
   *
   * @return string
   */
  private function getContentType(): string {
    // Get file extension
    $file_extension = pathinfo($this->mock->filename, PATHINFO_EXTENSION);

    return $this->output_extensions[$file_extension] ?? $this->output_extensions['default'];

  }


  /**
   * @return void
   */
  private function parsePlaceholderVariables(): void {
    $variables = [];
    // See if the url contains any placeholders.
    if (str_contains($this->mock->api, '{{')) {
      $api_args = explode('/', $this->mock->api);
      // Parse placeholders
      foreach ($api_args as $key => $api_arg) {
        // Not all args can contain placeholders so we need to check if it does.
        if (str_contains($api_arg, '{{')) {
          $variables[preg_replace('{{|}}', '', $api_arg)] = $this->request_url_args[$key];
        }
      }
    }

    if ($variables) {
      $this->mock->variables = array_merge($this->mock->variables, $variables);
    }
  }

}

