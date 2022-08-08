<?php

namespace Tests;

use App\Models\AdminUser;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;

abstract class ApiTestCase extends TestCase
{
  use RefreshDatabase;
  use WithFaker;

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

  protected $hiden = [
    'password',
  ];
  protected $noAssert = false;
  protected $noAssertAways = false;

  /** @var User|AdminUser|null $user */
  public ?string $role = null;
  public $user = null;


  protected function setUp(): void
  {
    parent::setUp();

    if ($this->role == 'admin') {
      $this->user =  AdminUser::first();
      $this->actingAs($this->user, 'admin');
    } else if ($this->role == 'customer') {
      $this->user =  User::first();
      $this->actingAs($this->user, 'api');
    }

    $this->noAssert = false;
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
        ->assertJsonStructure($this->modelSchema)
        ->assertJson([(new $this->model)->getKeyName() => $id]);
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
        ->assertJson(array_diff_key($modelCreate, array_flip($this->hiden)));
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
        ->assertJson(array_diff_key($modelUpdate, array_flip($this->hiden)));
    } else {
      $response->assertStatus($status);
    }

    return $response;
  }
}
