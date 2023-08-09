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
 * Class TaxId
 * 
 * @property int $id
 * @property int $user_id
 * @property string $dr_tax_id
 * @property string $country
 * @property string $customer_type
 * @property string $type
 * @property string $value
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models\Base
 */
class TaxId extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'tax_ids';

  protected $casts = [
    'user_id' => 'int'
  ];

  protected $fillable = [
    'user_id',
    'dr_tax_id',
    'country',
    'customer_type',
    'type',
    'value',
    'status'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
