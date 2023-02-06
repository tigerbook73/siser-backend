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
 * Class Country
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $currency
 * @property float $processing_fee_rate
 * @property bool $explicit_processing_fee
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class Country extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'countries';

  protected $casts = [
    'processing_fee_rate' => 'float',
    'explicit_processing_fee' => 'bool'
  ];

  protected $fillable = [
    'code',
    'name',
    'currency',
    'processing_fee_rate',
    'explicit_processing_fee'
  ];
}
