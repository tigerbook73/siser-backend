<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\SdrSalesSummary;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SdrPayout
 * 
 * @property string $id
 * @property Carbon $payoutTime
 * @property string $currency
 * @property float $amount
 * @property array $data
 * 
 * @property Collection|SdrSalesSummary[] $sdr_sales_summaries_where_payout_id
 *
 * @package App\Models\Base
 */
class SdrPayout extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'sdr_payouts';
  public $incrementing = false;
  public $timestamps = false;

  protected $casts = [
    'payoutTime' => 'datetime',
    'amount' => 'float',
    'data' => 'json'
  ];

  protected $fillable = [
    'payoutTime',
    'currency',
    'amount',
    'data'
  ];

  public function sdr_sales_summaries_where_payout_id()
  {
    return $this->hasMany(SdrSalesSummary::class, 'payoutId');
  }
}
