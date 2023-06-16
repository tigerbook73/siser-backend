<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LdsInstance;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LdsLog
 * 
 * @property int $id
 * @property int $lds_instance_id
 * @property string $action
 * @property string $result
 * @property string|null $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property LdsInstance $lds_instance
 *
 * @package App\Models\Base
 */
class LdsLog extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'lds_logs';

  protected $casts = [
    'lds_instance_id' => 'int'
  ];

  protected $fillable = [
    'lds_instance_id',
    'action',
    'result',
    'text'
  ];

  public function lds_instance()
  {
    return $this->belongsTo(LdsInstance::class);
  }
}
