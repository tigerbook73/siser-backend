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
 * Class GeneralConfiguration
 * 
 * @property int $id
 * @property string $name
 * @property array $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class GeneralConfiguration extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'general_configuration';

  protected $casts = [
    'value' => 'json'
  ];

  protected $fillable = [
    'name',
    'value'
  ];
}
