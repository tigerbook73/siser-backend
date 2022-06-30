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
 * @property int $machine_license_unit
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
    'machine_license_unit' => 'int'
  ];

  protected $fillable = [
    'machine_license_unit'
  ];
}
