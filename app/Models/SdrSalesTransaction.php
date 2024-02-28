<?php

namespace App\Models;

use App\Models\Base\SdrSalesTransaction as BaseSdrSalesTransaction;
use DigitalRiver\ApiSdk\Model\SalesTransaction as DrSalesTransaction;

class SdrSalesTransaction extends BaseSdrSalesTransaction
{
  static public function dataFromDrObject(DrSalesTransaction $drSalesTransaction)
  {
    $data = [];
    $data['id']                 = $drSalesTransaction->getId();
    $data['salesSummaryId']     = $drSalesTransaction->getSalesSummaryId();
    $data['saleTime']           = $drSalesTransaction->getSaleTime();
    $data['currency']           = $drSalesTransaction->getCurrency();
    $data['amount']             = $drSalesTransaction->getAmount();
    $data['type']               = $drSalesTransaction->getType();
    $data['orderId']            = $drSalesTransaction->getOrderId();
    $data['orderUpstreamId']    = $drSalesTransaction->getOrderUpstreamId();
    $data['billToCountry']                    = $drSalesTransaction->getBillToCountry();
    $data['payoutAmounts_currency']           = $drSalesTransaction->getPayoutAmounts()->getCurrency();
    $data['payoutAmounts_amount']             = $drSalesTransaction->getPayoutAmounts()->getAmount();
    $data['payoutAmounts_tax']                = $drSalesTransaction->getPayoutAmounts()->getTax();
    $data['payoutAmounts_productPrice']       = $drSalesTransaction->getPayoutAmounts()->getProductPrice();
    $data['payoutAmounts_digitalRiverShare']  = $drSalesTransaction->getPayoutAmounts()->getDigitalRiverShare();
    $data['payoutAmounts_transactionFees']    = $drSalesTransaction->getPayoutAmounts()->getTransactionFees();
    $data['payoutAmounts_payoutAmount']       = $drSalesTransaction->getPayoutAmounts()->getPayoutAmount();


    $data['data'] = json_encode($drSalesTransaction->jsonSerialize());
    return $data;
  }
}
