<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Machine
 * 
 * @property int $id
 * @property string $serial_no
 * @property string $model
 * @property string|null $nickname
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models\Base
 */
class Machine extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'machines';

  protected $casts = [
    'user_id' => 'int'
  ];

  protected $fillable = [
    'serial_no',
    'model',
    'nickname',
    'user_id'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
