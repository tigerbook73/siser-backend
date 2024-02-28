<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SdrConfiguration
 * 
 * @property string $name
 * @property string|null $value
 *
 * @package App\Models\Base
 */
class SdrConfiguration extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'sdr_configuration';
  protected $primaryKey = 'name';
  public $incrementing = false;
  public $timestamps = false;

  protected $fillable = [
    'value'
  ];
}
