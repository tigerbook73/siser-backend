<?php

namespace App\Models;

use App\Models\Base\SdrSalesSummary as BaseSdrSalesSummary;
use DigitalRiver\ApiSdk\Model\SalesSummary as DrSalesSummary;

class SdrSalesSummary extends BaseSdrSalesSummary
{
  static public function dataFromDrObject(DrSalesSummary $drSalesSummary)
  {
    $data = [];
    $data['id']               = $drSalesSummary->getId();
    $data['payoutId']         = $drSalesSummary->getPayoutId();
    $data['salesClosingTime'] = $drSalesSummary->getSalesClosingTime();
    $data['currency']         = $drSalesSummary->getCurrency();
    $data['amount']           = $drSalesSummary->getAmount();
    $data['paid']             = $drSalesSummary->getPaid();


    $data['data'] = json_encode($drSalesSummary->jsonSerialize());
    return $data;
  }
}
