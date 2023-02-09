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
 * Class StatisticRecord
 * 
 * @property int $id
 * @property Carbon $date
 * @property array $record
 *
 * @package App\Models\Base
 */
class StatisticRecord extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'statistic_records';
  public $timestamps = false;

  protected $casts = [
    'record' => 'json'
  ];

  protected $dates = [
    'date'
  ];

  protected $fillable = [
    'date',
    'record'
  ];
}
