<?php

namespace App\Console\Commands;

class LastRecord
{
  public function __construct(
    public string $type,
    bool $reset = false,
    public $path = '/tmp/last_record/'
  ) {
    if ($reset) {
      $this->setLast(0);
    }
  }

  public function setLast(int $id): void
  {
    // open temp file /tmp/last_record/$type.tmp
    // write id to file
    $file = $this->path . $this->type . '.tmp';
    if (!file_exists($file)) {
      if (!is_dir($this->path))
        mkdir(dirname($file), 0777, true);
    }
    file_put_contents($file, $id);
  }

  public function getLast(): int
  {
    // open temp file /tmp/last_record/$type.tmp
    // read id from file
    $file = $this->path . $this->type . '.tmp';
    if (!file_exists($file)) {
      return 0;
    }
    $id = file_get_contents($file);
    return (int)$id ?: 0;
  }
}
