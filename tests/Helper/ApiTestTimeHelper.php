<?php

namespace FakeTime {
  class FakeTime
  {
    static public $currentTime = null;
  }
};

namespace Tests\Helper {

  use Carbon\Carbon;

  class ApiTestTimeHelper
  {
    static public function setCurrentTime($time)
    {
      Carbon::setTestNow(Carbon::createFromTimestamp($time));
      \FakeTime\FakeTime::$currentTime = $time;
    }

    static public function unsetCurrentTime()
    {
      Carbon::setTestNow();
      \FakeTime\FakeTime::$currentTime = null;
    }

    static public function getTime()
    {
      return \FakeTime\FakeTime::$currentTime ?? \time();
    }
  }
}
