<?php

namespace App\Models;

use App\Models\Base\Invoice as BaseInvoice;

class Invoice extends BaseInvoice
{
  use TraitStatusTransition;
  use TraitDrAttr;

  // status -- see invoice.md
  public const STATUS_INIT            = 'init';         // first invoice only
  public const STATUS_OPEN            = 'open';         // renew invoice only
  public const STATUS_PENDING         = 'pending';
  public const STATUS_CANCELLED       = 'cancelled';
  public const STATUS_PROCESSING      = 'processing';   // first invoice only
  public const STATUS_FAILED          = 'failed';
  public const STATUS_COMPLETED       = 'completed';
  public const STATUS_REFUNDING       = 'refunding';
  public const STATUS_REFUND_FAILED   = 'refund-failed';
  public const STATUS_REFUNDED        = 'refunded';
  public const STATUS_PARTLY_REFUNDED = 'partly-refunded';

  // dr attributes
  public const DR_FILE_ID           = 'file_id';
  public const DR_INVOICE_ID        = 'invoice_id';
  public const DR_ORDER_ID          = 'order_id';

  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_id'     => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period_start_date'   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period_end_date'     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'billing_info'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'tax_id_info'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_info'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'payment_method_info' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_tax'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_amount'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_refunded'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'invoice_date'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'pdf_file'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'credit_memos'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function getDrFileId()
  {
    return $this->getDrAttr(self::DR_FILE_ID);
  }

  public function getDrInvoiceId()
  {
    return $this->getDrAttr(self::DR_INVOICE_ID);
  }

  public function getDrOrderId()
  {
    return $this->getDrAttr(self::DR_ORDER_ID);
  }

  public function setDrFileId(string $drFileId)
  {
    return $this->setDrAttr(self::DR_FILE_ID, $drFileId);
  }

  public function setDrInvoiceId(string $drInvoiceId)
  {
    $this->dr_invoice_id = $drInvoiceId;
    return $this->setDrAttr(self::DR_INVOICE_ID, $drInvoiceId);
  }

  public function setDrOrderId(string|null $drOrderId)
  {
    $this->dr_order_id = $drOrderId;
    return $this->setDrAttr(self::DR_ORDER_ID, $drOrderId ?? '');
  }

  static public function findByDrOrderId(string $drOrderId): Invoice|null
  {
    return self::where('dr_order_id', $drOrderId)->first();
  }

  public function isCompleted(): bool
  {
    return $this->status == self::STATUS_COMPLETED ||
      $this->status == self::STATUS_REFUNDED ||
      $this->status == self::STATUS_PARTLY_REFUNDED ||
      $this->status == self::STATUS_REFUND_FAILED ||
      $this->status == self::STATUS_REFUNDING;
  }

  public function addCreditMemo(string $fileId, string $url)
  {
    $creditMemos = $this->credit_memos ?? [];

    $found = $this->findCreditMemoByFileId($fileId);
    if ($found !== false) {
      $creditMemos[$found] = [
        'file_id'     => $fileId,
        'url'         => $url,
        'created_at'  => now(),
      ];
    } else {
      $creditMemos[] = [
        'file_id'     => $fileId,
        'url'         => $url,
        'created_at'  => now(),
      ];
    }
    $this->credit_memos = $creditMemos;
    return $this;
  }

  public function findCreditMemoByFileId(string $fileId): int|false
  {
    $creditMemos = $this->credit_memos ?? [];
    $count = count($creditMemos);

    for ($index = 0; $index < $count; $index++) {
      if ($creditMemos[$index]['file_id'] ?? "" == $fileId) {
        return $index;
      }
    }

    return false;
  }

  public function getActiveRefund(): Refund|null
  {
    return $this->refunds()->where('status', Refund::STATUS_PENDING)->first();
  }
}
