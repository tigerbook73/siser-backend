<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DrEventRawRecord
 * 
 * @property int $id
 * @property string $event_id
 * @property array $data
 *
 * @package App\Models\Base
 */
class DrEventRawRecord extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'dr_event_raw_records';
  public $timestamps = false;

  protected $casts = [
    'data' => 'json'
  ];

  protected $fillable = [
    'event_id',
    'data'
  ];
}
