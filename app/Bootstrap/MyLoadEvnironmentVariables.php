<?php

namespace App\Bootstrap;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Env;

class MyLoadEvnironmentVariables extends LoadEnvironmentVariables
{
  public function bootstrap(Application $app)
  {
    // Call the parent bootstrap to load default .env variables
    parent::bootstrap($app);

    // Access and expand the FULL variable
    $jsonEnvString = env('JSON_ENVS', '{}');
    $jsonEnvs = json_decode($jsonEnvString, true);

    if (json_last_error() === JSON_ERROR_NONE) {
      $repo = Env::getRepository();
      foreach ($jsonEnvs as $key => $value) {
        // Dynamically set the variables
        $repo->set($key, $value);
      }
    }
  }
}
