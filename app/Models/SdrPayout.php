<?php

namespace App\Models;

use App\Models\Base\SdrPayout as BaseSdrPayout;
use DigitalRiver\ApiSdk\Model\Payout as DrPayout;

class SdrPayout extends BaseSdrPayout
{
  static public function dataFromDrObject(DrPayout $drPayout)
  {
    $data = [];
    $data['id']           = $drPayout->getId();
    $data['payoutTime']   = $drPayout->getPayoutTime();
    $data['currency']     = $drPayout->getCurrency();
    $data['amount']       = $drPayout->getAmount();

    $data['data'] = json_encode($drPayout->jsonSerialize());
    return $data;
  }
}
