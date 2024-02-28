<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\SdrSalesSummary;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SdrSalesTransaction
 * 
 * @property string $id
 * @property string|null $salesSummaryId
 * @property Carbon $saleTime
 * @property string $currency
 * @property float $amount
 * @property string $type
 * @property string $orderId
 * @property string|null $orderUpstreamId
 * @property string $billToCountry
 * @property string $payoutAmounts_currency
 * @property float $payoutAmounts_amount
 * @property float $payoutAmounts_tax
 * @property float $payoutAmounts_productPrice
 * @property float $payoutAmounts_digitalRiverShare
 * @property float $payoutAmounts_transactionFees
 * @property float $payoutAmounts_payoutAmount
 * @property array $data
 * 
 * @property SdrSalesSummary|null $sales_summary_id
 *
 * @package App\Models\Base
 */
class SdrSalesTransaction extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'sdr_sales_transactions';
  public $incrementing = false;
  public $timestamps = false;

  protected $casts = [
    'saleTime' => 'datetime',
    'amount' => 'float',
    'payoutAmounts_amount' => 'float',
    'payoutAmounts_tax' => 'float',
    'payoutAmounts_productPrice' => 'float',
    'payoutAmounts_digitalRiverShare' => 'float',
    'payoutAmounts_transactionFees' => 'float',
    'payoutAmounts_payoutAmount' => 'float',
    'data' => 'json'
  ];

  protected $fillable = [
    'salesSummaryId',
    'saleTime',
    'currency',
    'amount',
    'type',
    'orderId',
    'orderUpstreamId',
    'billToCountry',
    'payoutAmounts_currency',
    'payoutAmounts_amount',
    'payoutAmounts_tax',
    'payoutAmounts_productPrice',
    'payoutAmounts_digitalRiverShare',
    'payoutAmounts_transactionFees',
    'payoutAmounts_payoutAmount',
    'data'
  ];

  public function sales_summary_id()
  {
    return $this->belongsTo(SdrSalesSummary::class, 'salesSummaryId');
  }
}
