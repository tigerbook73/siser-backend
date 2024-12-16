<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PaddleMap
 * 
 * @property int $id
 * @property string $paddle_id
 * @property int $model_id
 * @property string $model_class
 * @property array|null $meta
 *
 * @package App\Models\Base
 */
class PaddleMap extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'paddle_maps';
  public $timestamps = false;

  protected $casts = [
    'model_id' => 'int',
    'meta' => 'json'
  ];

  protected $fillable = [
    'paddle_id',
    'model_id',
    'model_class',
    'meta'
  ];
}
