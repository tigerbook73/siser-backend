<?php

namespace FakeTime {
  class FakeTime
  {
    static public $currentTime = null;
  }
};

namespace Tests\Helper {

  class ApiTestTimeHelper
  {
    static public function setCurrentTime($time)
    {
      \FakeTime\FakeTime::$currentTime = $time;
    }

    static public function unsetCurrentTime()
    {
      \FakeTime\FakeTime::$currentTime = null;
    }

    static public function getTime()
    {
      return \FakeTime\FakeTime::$currentTime ?? \time();
    }
  }
}
