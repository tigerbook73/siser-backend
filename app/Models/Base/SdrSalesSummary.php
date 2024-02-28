<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\SdrPayout;
use App\Models\SdrSalesTransaction;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SdrSalesSummary
 * 
 * @property string $id
 * @property string|null $payoutId
 * @property Carbon $salesClosingTime
 * @property string $currency
 * @property float $amount
 * @property bool $paid
 * @property array $data
 * 
 * @property SdrPayout|null $payout_id
 * @property Collection|SdrSalesTransaction[] $sdr_sales_transactions_where_sales_summary_id
 *
 * @package App\Models\Base
 */
class SdrSalesSummary extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'sdr_sales_summaries';
  public $incrementing = false;
  public $timestamps = false;

  protected $casts = [
    'salesClosingTime' => 'datetime',
    'amount' => 'float',
    'paid' => 'bool',
    'data' => 'json'
  ];

  protected $fillable = [
    'payoutId',
    'salesClosingTime',
    'currency',
    'amount',
    'paid',
    'data'
  ];

  public function payout_id()
  {
    return $this->belongsTo(SdrPayout::class, 'payoutId');
  }

  public function sdr_sales_transactions_where_sales_summary_id()
  {
    return $this->hasMany(SdrSalesTransaction::class, 'salesSummaryId');
  }
}
