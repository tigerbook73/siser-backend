<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MigrationFunction
 * 
 * @property int $id
 * @property string $function
 * @property array|null $data
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class MigrationFunction extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'migration_functions';

  protected $casts = [
    'data' => 'json'
  ];

  protected $fillable = [
    'function',
    'data',
    'status'
  ];
}
