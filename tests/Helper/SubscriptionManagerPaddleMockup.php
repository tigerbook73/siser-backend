<?php

namespace Tests\Helper;

use App\Services\SubscriptionManager\SubscriptionManagerResult;
use App\Services\LicenseSharing\LicenseSharingService;
use App\Services\Paddle\PaddleService;
use App\Services\Paddle\SubscriptionManagerPaddle;

class SubscriptionManagerPaddleMockup extends SubscriptionManagerPaddle
{
  public function __construct(
    public PaddleService $paddleService,
    public LicenseSharingService $licenseService,
    public SubscriptionManagerResult $result,
  ) {
    $this->paddleService = new PaddleServiceMockup($this);

    parent::__construct($paddleService, $licenseService, $result);

    $this->addressService       = new AddressServiceMockup($this);
    $this->businessService      = new BusinessServiceMockup($this);
    $this->customerService      = new CustomerServiceMockup($this);
    $this->discountService      = new DiscountServiceMockup($this);
    $this->paymentMethodService = new PaymentMethodServiceMockup($this);
    $this->priceService         = new PriceServiceMockup($this);
    $this->productService       = new ProductServiceMockup($this);
    $this->subscriptionService  = new SubscriptionServiceMockup($this);
    $this->transactionService   = new TransactionServiceMockup($this);
  }
}
