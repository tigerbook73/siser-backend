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
    $this->listAssert(200, ['country' => 'US']);

    $this->listAssert(200, ['country' => 'US', 'name' => 'Leonardo® Design Studio Pro Monthly Plan']);

    $this->listAssert(200, ['country' => 'US', 'product_name' => 'Leonardo® Design Studio Basic'], 0);

    $this->listAssert(200, ['country' => 'US', 'product_name' => 'Leonardo® Design Studio Pro']);

    $this->listAssert(200, ['country' => $this->object->price_list[0]['country'], 'product_name' => $this->object->product_name]);

    $this->markTestIncomplete('more filter to do');
  }

  public function testPlanListError()
  {
    $this->markTestIncomplete('more filter to do');
  }
}
