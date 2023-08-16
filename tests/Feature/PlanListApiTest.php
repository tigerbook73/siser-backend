<?php

namespace Tests\Feature;

class PlanListApiTest extends PlanTestCase
{
  public ?string $role = 'customer';

  public function listAssert($status = 200, $params = [], $count = null)
  {
    $paramString = http_build_query($params);
    $response = $this->getJson($this->baseUrl . ($paramString ? "?$paramString" : ""));

    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure([
          'data' => ['*' => $this->modelSchema]
        ]);

      if ($count !== null) {
        $this->assertEquals(count($response->json()['data']), $count);
      }
    } else {
      $response->assertStatus($status);
    }

    return $response;
  }


  public function testPlanListSuccess()
  {
    $this->listAssert(200, ['country' => 'US'], 1);

    $this->listAssert(200, ['country' => 'US', 'name' => 'Leonardo™ Design Studio Pro Monthly Plan'], 1);

    $this->listAssert(200, ['country' => 'US', 'catagory' => 'machine'], 1);

    $this->listAssert(200, ['country' => 'US', 'catagory' => 'software'], 0);

    $this->listAssert(200, ['country' => $this->object->price_list[0]['country'], 'catagory' => $this->object->catagory], 1);

    $this->markTestIncomplete('more filter to do');
  }

  public function testPlanListError()
  {
    $this->markTestIncomplete('more filter to do');
  }
}
