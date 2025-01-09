<?php

namespace Tests;

use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Trait\CognitoProviderMockup;
use Tests\Trait\SubscriptionManagerPaddleMockup;

abstract class ApiTestCase extends TestCase
{
  use RefreshDatabase;
  use WithFaker;
  use CognitoProviderMockup;
  use SubscriptionManagerPaddleMockup;

  /**
   * Indicates whether the default seeder should run before each test.
   *
   * @var bool
   */
  protected $seed = true;

  /**
   * the following properties may redefine by extended class
   */
  public string $baseUrl;
  public string $model;

  public $modelSchema = [];
  public $modelCreate = [];
  public $modelUpdate = [];

  protected $hidden = [
    'password',
  ];
  protected $noAssert = false;
  protected $noAssertAways = false;

  /** @var User|AdminUser|null $user */
  public $user = null;
  public ?string $role = null;


  protected function setUp(): void
  {
    parent::setUp();

    $this->actingAsDefault();

    $this->noAssert = false;
  }

  protected function actingAsCustomer()
  {
    $this->actingAs(User::first(), 'api');
  }

  protected function actingAsAdmin()
  {
    $this->actingAs(AdminUser::first(), 'admin');
  }

  protected function actingAsDefault()
  {
    if ($this->role == 'admin') {
      $this->user =  AdminUser::first();
      $this->actingAs($this->user, 'admin');
    } else if ($this->role == 'customer') {
      $this->user =  User::first();
      $this->actingAs($this->user, 'api');
    }
  }

  protected function tearDown(): void
  {
    parent::tearDown();
  }

  /**
   * the following are helper function
   */

  protected function modelCount($conditions = [], $limit = -1): int
  {
    return count($this->model::where($conditions)->limit($limit)->get());
  }

  public function listAssert($status = 200, $params = [], $count = null)
  {
    $paramString = http_build_query($params);
    $count = $count ?? $this->modelCount($params);

    $response = $this->getJson($this->baseUrl . ($paramString ? "?$paramString" : ""));

    if ($this->noAssertAways || $this->noAssert) {
      return $response;
    }

    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure([
          'data' => ['*' => $this->modelSchema]
        ]);

      $this->assertEquals(count($response->json()['data']), $count);
    } else {
      $response->assertStatus($status);
    }

    return $response;
  }

  public function getAssert($status = 200, $id = 999999999, $params = [])
  {
    $paramString = http_build_query($params);

    $response = $this->getJson($this->baseUrl . '/' . $id . ($paramString ? "?$paramString" : ""));

    if ($this->noAssertAways || $this->noAssert) {
      return $response;
    }

    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure($this->modelSchema);
    } else {
      $response->assertStatus($status);
    }

    return $response;
  }

  public function createAssert($status = 201)
  {
    $modelCreate = $this->modelCreate;

    $response = $this->postJson($this->baseUrl, $modelCreate);

    if ($this->noAssertAways || $this->noAssert) {
      return $response;
    }
    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure($this->modelSchema)
        ->assertJson(array_diff_key($modelCreate, array_flip($this->hidden)));
    } else {
      $response->assertStatus($status);
    }

    return $response;
  }

  public function updateAssert($status = 500, $id = 99999999)
  {
    $modelUpdate = $this->modelUpdate;

    $response = $this->patchJson("$this->baseUrl/$id", $modelUpdate);

    if ($this->noAssertAways || $this->noAssert) {
      return $response;
    }

    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure($this->modelSchema)
        ->assertJson(array_diff_key($modelUpdate, array_flip($this->hidden)));
    } else {
      $response->assertStatus($status);
    }

    return $response;
  }

  /**
   * Create dummy string for testing field's maxlength.
   */
  public function createRandomString(int $strLength): string
  {
    return $this->faker->regexify('[A-Za-z0-9$&+,:;=?@#|<>.^*()%!-]{' . $strLength . '}');
  }

  /**
   * assert response successfully
   * @param \Illuminate\Testing\TestResponse $response
   */
  public function assertSuccess($response)
  {
    $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300, 'response is not successful');
  }

  /**
   * assert response successfully
   * @param \Illuminate\Testing\TestResponse $response
   */
  public function assertFailed($response)
  {
    $this->assertTrue($response->getStatusCode() >= 400 && $response->getStatusCode() < 500, 'response is not failed');
  }
}
