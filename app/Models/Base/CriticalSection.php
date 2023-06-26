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
 * Class CriticalSection
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property int $object_id
 * @property array $action
 * @property array $steps
 * @property string $status
 * @property bool $need_notify
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class CriticalSection extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'critical_sections';

  protected $casts = [
    'user_id' => 'int',
    'object_id' => 'int',
    'action' => 'json',
    'steps' => 'json',
    'need_notify' => 'bool'
  ];

  protected $fillable = [
    'user_id',
    'type',
    'object_id',
    'action',
    'steps',
    'status',
    'need_notify'
  ];
}
