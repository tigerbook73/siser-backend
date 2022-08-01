<?php

namespace Tests;

use App\Models\AdminUser;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class ApiTestCase extends TestCase
{
  use RefreshDatabase;

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

  /** @var User|AdminUser|null $user */
  public ?string $role = null;
  public $user = null;

  /**
   * faker helper
   */
  public ?Generator $faker = null;

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

    if (!$this->faker) {
      $this->faker = app()->make(Generator::class);
    }
  }

  protected function tearDown(): void
  {
    parent::tearDown();
  }

  /**
   * the following are helper function
   */

  public function modelCount($conditions = [], $limit = -1): int
  {
    return count($this->model::where($conditions)->limit(-1)->get());
  }

  public function listAssert($status = 200, $params = [], $count = null)
  {
    $paramString = http_build_query($params);
    $count = $count ?? $this->modelCount($params);

    $response = $this->getJson($this->baseUrl . ($paramString ? "?$paramString" : ""));

    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure([
          'data' => ['*' => $this->modelSchema]
        ]);

      $this->assertEquals(count($response->json()['data']), $count);
    } else {
      $response->assert($status);
    }

    return $response;
  }

  public function getAssert($status = 200, $id = 999999999, $params = [])
  {
    $paramString = http_build_query($params);

    $response = $this->getJson($this->baseUrl . '/' . $id . ($paramString ? "?$paramString" : ""));

    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure($this->modelSchema)
        ->assertJson([(new $this->model)->getKeyName() => $id]);
    } else {
      $response->assert($status);
    }

    return $response;
  }

  public function createAssert($status = 201)
  {
    $modelCreate = $this->modelCreate;

    $response = $this->postJson($this->baseUrl, $modelCreate);

    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure($this->modelSchema)
        ->assertJson(array_diff_key($modelCreate, array_flip($this->hiden)));
    } else {
      $response->assert($status);
    }

    return $response;
  }

  public function updateAssert($status = 500, $id = 99999999)
  {
    $modelUpdate = $this->modelUpdate;

    $response = $this->patchJson("$this->baseUrl/$id", $modelUpdate);

    if ($status >= 200 && $status < 300) {
      $response->assertStatus($status)
        ->assertJsonStructure($this->modelSchema)
        ->assertJson(array_diff_key($modelUpdate, array_flip($this->hiden)));
    } else {
      $response->assert($status);
    }

    return $response;
  }
}
