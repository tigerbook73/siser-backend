<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 * 
 * @property int $id
 * @property string $country_code
 * @property string $country
 *
 * @package App\Models\Base
 */
class Country extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'countries';
  public $timestamps = false;

  protected $fillable = [
    'country_code',
    'country'
  ];
}
