<?php

namespace App\Models;

use App\Models\Base\MigrationFunction as BaseMigrationFunction;

class MigrationFunction extends BaseMigrationFunction
{
  const STATUS_INIT = 'init';
  const STATUS_ONGOING = 'ongoing';
  const STATUS_SUCCESS = 'success';

  static public function functionExists(string $function): bool
  {
    return MigrationFunction::where('function', $function)->exists();
  }

  static public function getFunction(string $function): ?MigrationFunction
  {
    return MigrationFunction::where('function', $function)->first();
  }

  static public function startFunction(string $function): ?MigrationFunction
  {
    $migrateFunction = MigrationFunction::getFunction(__METHOD__);
    if ($migrateFunction) {
      return null;
    }

    $migrateFunction = new MigrationFunction();
    $migrateFunction->function = __METHOD__;
    $migrateFunction->data = null;
    $migrateFunction->status = MigrationFunction::STATUS_ONGOING;
    $migrateFunction->save();
    return $migrateFunction;
  }

  public function updateData(array $data): MigrationFunction
  {
    $this->data = $data;
    $this->save();
    return $this;
  }

  public function complete()
  {
    $this->status = MigrationFunction::STATUS_SUCCESS;
    $this->save();
    return $this;
  }
}
