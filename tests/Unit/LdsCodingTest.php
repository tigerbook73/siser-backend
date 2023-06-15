<?php

namespace Tests\Unit;

use App\Services\Lds\LdsCoding;
use Tests\TestCase;

class LdsCodingTest extends TestCase
{
  public function testUserIdEncodeAndDecode()
  {
    $userIds = [1, 2, 10, 100, 10000, 100000, 1000000, 3000000];

    $ldsCoding = new LdsCoding();
    foreach ($userIds as $userId) {
      $encoded = $ldsCoding->encodeUserId($userId);
      $this->assertNotNull($encoded);
      $this->assertEquals(15, strlen($encoded ?? ""));
      $this->assertEquals($userId, $ldsCoding->decodeUserId($encoded));
    }
  }

  public function testJsonTextEncodeAndDecode()
  {
    $ldsCoding = new LdsCoding();
    for ($i = 0; $i < 10; $i++) {
      $text = random_bytes(100);
      $encoded = $ldsCoding->encodeJsonText($text);
      $this->assertNotNull($encoded);
      $this->assertEquals($text, $ldsCoding->decodeJsonText($encoded));
    }
  }
}
