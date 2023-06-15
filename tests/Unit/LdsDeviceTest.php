<?php

namespace Tests\Unit;

use App\Models\LdsDevice;
use Tests\TestCase;

class LdsDeviceTest extends TestCase
{
  public function init()
  {
    return LdsDevice::init([
      'device_id'   => 'test_device_id',
      'user_code'   => 'test_user_code',
      'device_name' => 'test_device_name',
    ]);
  }

  public function testSetAndGetOk()
  {
    $ldsDevice = $this->init();

    // test simple set & get
    $value = '12345678';
    $this->assertEquals($value, $ldsDevice->setDeviceId($value)->getDeviceId());
    $this->assertEquals($value, $ldsDevice->setDeviceName($value)->getDeviceName());
    $this->assertEquals($value, $ldsDevice->setUserCode($value)->getUserCode());
    $this->assertEquals($value, $ldsDevice->setStatus($value)->getStatus());
    $this->assertEquals($value, $ldsDevice->setStatus($value)->getStatus());
    $this->assertEquals((int)$value, $ldsDevice->setExpiresAt((int)$value)->getExpiresAt());
    $this->assertEquals($value, $ldsDevice->setStatus($value)->getStatus());

    // test latest action set & get
    $action = [
      'action'      => 'test',
      'client_ip'   => '192.168.1.1',
      'time'        => time(),
    ];
    $result = $ldsDevice->setLatestAction($action['action'], $action['client_ip'])->getLatestAction();
    $this->assertEquals($action['action'], $result['action']);
    $this->assertEquals($action['client_ip'], $result['client_ip']);
    $this->assertLessThan(2, $result['time'] - $action['time']);

    $result = $ldsDevice->setLatestAction($action['action'])->getLatestAction();
    $this->assertEquals('', $result['client_ip']);
  }

  public function testRegisterOk()
  {
    $ldsDevice = $this->init();

    $action = [
      'action'      => '',
      'client_ip'   => '192.168.1.1',
      'time'        => time(),
    ];

    // register new
    $result = $ldsDevice->register($action['client_ip']);

    $this->assertEquals('offline', $ldsDevice->getStatus());
    $this->assertEquals(0, $ldsDevice->getExpiresAt());
    $this->assertEquals('register', $ldsDevice->getLatestAction()['action']);
    $this->assertEquals($action['client_ip'], $ldsDevice->getLatestAction()['client_ip']);
    $this->assertLessThan(2, $ldsDevice->getLatestAction()['time'] - $action['time']);

    // register existing
    $ldsDevice->setLatestAction('abc', 'localhost');
    $ldsDevice->register($action['client_ip']);

    $this->assertEquals('offline', $ldsDevice->getStatus());
    $this->assertEquals(0, $ldsDevice->getExpiresAt());
    $this->assertEquals('register', $ldsDevice->getLatestAction()['action']);
    $this->assertEquals($action['client_ip'], $ldsDevice->getLatestAction()['client_ip']);
    $this->assertLessThan(2, $ldsDevice->getLatestAction()['time'] - $action['time']);
  }
}
