<?php


class Mock {

  public string $api;
  public string $method = 'GET';
  public string $filename;
  public string $custom_logic_function = '';
  public array $variables = [];


  /**
   * @param array $api
   */
  public function __construct(array $api) {
    $this->api = $api['api'];
    $this->method = $api['method'];
    $this->filename = $api['filename'];
    $this->custom_logic_function = $api['custom_logic_function'];
    $this->variables = $api['variables'];
  }


  /**
   * Get processed content.
   *
   * @return string
   */
  public function getContent(): string {

    $filename = 'apis/' . $this->filename;

    $content = file_get_contents($filename);

    // if $custom_logic_function function exists execute it
    if ($this->custom_logic_function) {

      $custom_logic_path =  __DIR__ . '/../dev/' . $this->custom_logic_function . '.php';

      if (file_exists($custom_logic_path)) {
        require $custom_logic_path;
      }
      else {
        return_response(500, 'Custom logic function ' . $this->custom_logic_function . ' not found.');
      }

      if (function_exists($this->custom_logic_function)) {
        // Execute the function from named $this->custom_logic_function variable
        // Created a new var as I didn't want to mess up with __call().
        $function_name = $this->custom_logic_function;
        $function_name($this->variables);
      }
    }

    foreach ($this->variables as $key => $value) {
      $content = str_replace('{{' . $key . '}}', $value, $content);
    }
    return $content;

  }

}
