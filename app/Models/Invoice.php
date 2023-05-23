<?php

namespace App\Models;

use App\Models\Base\Invoice as BaseInvoice;

class Invoice extends BaseInvoice
{
  // status -- see invoice.md
  public const STATUS_COMPLETED     = 'completed';
  public const STATUS_COMPLETING    = 'completing';
  public const STATUS_FAILED        = 'failed';
  public const STATUS_OPEN          = 'open';
  public const STATUS_PENDING       = 'pending';
  public const STATUS_VOID          = 'void';

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
    'plan_info'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'processing_fee_info' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_tax'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_amount'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'invoice_date'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'pdf_file'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function getDrAttr(string $attr): string|null
  {
    return $this->dr[$attr] ?? null;
  }

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

  public function setDrAttr(string $attr, string $value)
  {
    $dr = $this->dr ?? [];
    $dr[$attr] = $value;
    $this->dr = $dr;
    return $this;
  }

  public function setFileId(string $file_id)
  {
    return $this->setDrAttr(self::DR_FILE_ID, $file_id);
  }

  public function setInvoiceId(string $invoice_id)
  {
    $this->dr_invoice_id = $invoice_id;
    return $this->setDrAttr(self::DR_INVOICE_ID, $invoice_id);
  }

  public function setOrderId(string $order_id)
  {
    $this->dr_order_id = $order_id;
    return $this->setDrAttr(self::DR_ORDER_ID, $order_id);
  }
}
