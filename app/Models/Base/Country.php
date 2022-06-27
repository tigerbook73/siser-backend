<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

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
  protected $table = 'countries';
  public $timestamps = false;

  protected $fillable = [
    'country_code',
    'country'
  ];
}
