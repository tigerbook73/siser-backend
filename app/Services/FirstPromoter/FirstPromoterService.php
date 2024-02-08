<?php

namespace App\Services\FirstPromoter;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirstPromoterService
{
  public $enabled = false;
  public $apiKey = "";
  public $client = null;

  public function __construct()
  {
    $this->enabled = config('affiliate.enabled');
    $this->apiKey = config('affiliate.first_promoter.api_key');
    $this->client = new Client([
      'base_uri' => 'https://firstpromoter.com/api/v1/',
      'headers'  => [
        'x-api-key' => $this->apiKey,
      ]
    ]);
  }

  public function trackSale(string|int $userId, string|int $invoiceId, float $amount, string $currency, string $planType = null, string $couponCode = null)
  {
    if (!$this->enabled) {
      return;
    }

    // mondatory fields
    $data = [
      'uid'         => config('affiliate.first_promoter.id_prefix') . $userId,
      'event_id'    => config('affiliate.first_promoter.id_prefix') . $invoiceId,
      'amount'      => round($amount * 100),
      'currency'    => $currency,
    ];
    // optional fields
    if ($planType)    $data['plan']       = $planType;
    if ($couponCode)  $data['promo_code'] = $couponCode;

    try {
      $response = $this->client->post('track/sale', ['form_params' => $data]);
      if ($response->getStatusCode() == 200) {
        log::info(
          'FP_LOG: track sale successfully.',
          [
            'req' => $data,
            'res' => json_decode($response->getBody()->getContents(), true)
          ],
        );
      } else {
        log::info('FP_LOG: track sale ignored.', $data);
      }
    } catch (\Exception $e) {
      log::warning("FP_LOG: {$e->getMessage()} .", $data);
    }
  }

  public function trackRefund(string|int $userId, string|int $invoiceId, string|int $refundId, float $amount, string $currency)
  {
    if (!$this->enabled) {
      return;
    }

    $data = [
      'uid'         => config('affiliate.first_promoter.id_prefix') . $userId,
      'event_id'    => config('affiliate.first_promoter.id_prefix') . $invoiceId . '_refund_' . $refundId,
      'amount'      => round($amount * 100),
      'currency'    => $currency,
      'sale_event_id' => config('affiliate.first_promoter.id_prefix') . $invoiceId,
    ];

    try {
      $response = $this->client->post('track/refund', ['form_params' => $data]);
      if ($response->getStatusCode() == 200) {
        log::info(
          'FP_LOG: track refund successfully.',
          [
            'req' => $data,
            'res' => json_decode($response->getBody()->getContents(), true)
          ],
        );
      } else {
        log::info('FP_LOG: track refund ignored.', $data);
      }
    } catch (\Exception $e) {
      log::warning("FP_LOG: {$e->getMessage()} .", $data);
    }
  }
}
