<?php

namespace App\Models;

class BillingInformation
{
  public function __construct(
    public int $user_id,
    public string $first_name,
    public string $last_name,
    public string $email,
    public string $phone,
    public string $customer_type,
    public string $organization,
    public BillingAddress $address,
    public string $language,
    public string $locale,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      user_id: $data['user_id'],
      first_name: $data['first_name'],
      last_name: $data['last_name'],
      email: $data['email'] ?? '',
      phone: $data['phone'] ?? '',
      customer_type: $data['customer_type'] ?? BillingInfo::CUSTOMER_TYPE_INDIVIDUAL,
      organization: $data['organization'] ?? '',
      address: BillingAddress::from($data['address']),
      language: $data['language'] ?? 'US',
      locale: $data['locale'] ?? 'en_US',
    );
  }

  public function toArray(): array
  {
    return [
      'user_id' => $this->user_id,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'email' => $this->email,
      'phone' => $this->phone,
      'customer_type' => $this->customer_type,
      'organization' => $this->organization,
      'address' => $this->address->toArray(),
      'language' => $this->language,
      'locale' => $this->locale,
    ];
  }
}
