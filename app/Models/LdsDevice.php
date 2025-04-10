<?php

namespace App\Models;

class LdsDevice
{
  private $container = [];

  /**
   * @param array $data ['device_id' => '...', 'user_code' => '...', 'device_name' => '...']
   */
  static public function init(array $data): self
  {
    $device = new self();
    $device->container = [
      'device_id'           => $data['device_id'],
      'user_code'           => $data['user_code'],
      'device_name'         => $data['device_name'] ?? '',
      'status'              => '',
      'registered_at'       => time(),
      'expires_at'          => 0,
      'latest_action'       => [
        'action'      => '',
        'client_ip'   => '',
        'time'        => 0,
      ],
    ];
    return $device;
  }

  static public function fromArray(array $data): self
  {
    $device = new self();
    $device->container = $data;
    return $device;
  }

  public function toArray(): array
  {
    return $this->container;
  }

  public function getUserCode(): string
  {
    return $this->container['user_code'];
  }

  public function setUserCode(string $user_code): self
  {
    $this->container['user_code'] = $user_code;
    return $this;
  }

  public function getDeviceId(): string
  {
    return $this->container['device_id'];
  }

  public function setDeviceId(string $device_id): self
  {
    $this->container['device_id'] = $device_id;
    return $this;
  }

  public function getDeviceName(): string
  {
    return $this->container['device_name'];
  }

  public function setDeviceName(string $device_name): self
  {
    $this->container['device_name'] = $device_name;
    return $this;
  }

  public function getStatus(): string
  {
    return $this->container['status'];
  }

  public function setStatus(string $status): self
  {
    $this->container['status'] = $status;
    return $this;
  }

  public function getExpiresAt(): int
  {
    return $this->container['expires_at'];
  }

  public function setExpiresAt(int $expires_at): self
  {
    $this->container['expires_at'] = $expires_at;
    return $this;
  }

  public function getLatestAction(): array
  {
    return $this->container['latest_action'];
  }

  public function setLatestAction(string $action, ?string $client_ip = null): self
  {
    $this->container['latest_action'] = [
      'action'    => $action,
      'client_ip' => $client_ip ?: '',
      'time'      => time(),
    ];
    return $this;
  }

  public function register(string $client_ip): self
  {
    $this->setStatus('offline')
      ->setExpiresAt(0)
      ->setLatestAction('register', $client_ip);
    return $this;
  }

  public function checkin(string $client_ip): self
  {
    $this->setStatus('online')
      ->setExpiresAt(time() + 3600)
      ->setLatestAction('checkin', $client_ip);
    return $this;
  }

  public function checkout(?string $client_ip = null): self
  {
    $this->setStatus('offline')
      ->setExpiresAt(0)
      ->setLatestAction('checkout', $client_ip);
    return $this;
  }
};
