<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

const NULL_STRING = "-99999999";

class SimpleController extends Controller
{
  // overridable variable
  protected string $modelClass;
  protected string $keyName = "";

  // login user information
  /** @var User|AdminUser|null $user */
  protected $user = null;
  protected string $userType = "";

  // query options
  protected string $orderAttr = "";        // default is primary key
  protected string $orderDirection = 'asc';  // default is 'desc'

  // default list filter
  private $defaultRules = [
    'limit' => ['integer', 'min:1', 'max:200'],
    'offset' => ['integer', 'min:0'],
    'search' => ['string'],
  ];

  // filter management
  public $opMap;

  public function __construct()
  {
    $this->opMap = [
      null    => ['op_len' => 0, 'param_count' => 1, 'fn' =>  fn ($q, $param, $v1) => $q->where($param, $v1)],
      '='     => ['op_len' => 1, 'param_count' => 1, 'fn' =>  fn ($q, $param, $v1) => $q->where($param, $v1)],
      '>'     => ['op_len' => 1, 'param_count' => 1, 'fn' =>  fn ($q, $param, $v1) => $q->where($param, '>', $v1)],
      '>='    => ['op_len' => 2, 'param_count' => 1, 'fn' =>  fn ($q, $param, $v1) => $q->where($param, '>=', $v1)],
      '<'     => ['op_len' => 1, 'param_count' => 1, 'fn' =>  fn ($q, $param, $v1) => $q->where($param, '<', $v1)],
      '<='    => ['op_len' => 2, 'param_count' => 1, 'fn' =>  fn ($q, $param, $v1) => $q->where($param, '<=', $v1)],
      '<>'    => ['op_len' => 2, 'param_count' => 1, 'fn' =>  fn ($q, $param, $v1) => $q->where($param, '<>', $v1)],
      '~'     => ['op_len' => 1, 'param_count' => 1, 'fn' =>  fn ($q, $param, $v1) => $q->where($param, 'like', '%' . preg_replace('/[, ]+/', '%', $v1) . '%')],
      '()'    => ['op_len' => 2, 'param_count' => 2, 'fn' =>  fn ($q, $param, $v1, $v2) => $q->where($param, '>', $v1)->where($param, '<', $v2)],
      '(]'    => ['op_len' => 2, 'param_count' => 2, 'fn' =>  fn ($q, $param, $v1, $v2) => $q->where($param, '>', $v1)->where($param, '<=', $v2)],
      '[)'    => ['op_len' => 2, 'param_count' => 2, 'fn' =>  fn ($q, $param, $v1, $v2) => $q->where($param, '>=', $v1)->where($param, '<', $v2)],
      '[]'    => ['op_len' => 2, 'param_count' => 2, 'fn' =>  fn ($q, $param, $v1, $v2) => $q->where($param, '>=', $v1)->where($param, '<=', $v2)],
    ];

    $model = new $this->modelClass();
    $this->keyName = $model->getKeyName();
    $this->orderAttr = $this->orderAttr ?: $this->keyName;
  }

  public function list(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateList($request);

    $objects = $this->standardQuery($inputs)->get();
    return ['data' => $this->transformMultipleResources($objects)];
  }

  public function index(int $id)
  {
    $this->validateUser();

    $object = $this->baseQuery()->findOrFail($id);
    return $this->transformSingleResource($object);
  }

  protected function transformSingleResource($object)
  {
    return $object->toResource($this->userType);
  }

  protected function transformMultipleResources($objects)
  {
    if (count($objects) > 0) {
      return array_map(function ($object) {
        return $object->toResource($this->userType);
      }, $objects->all());
    }
    return [];
  }

  public function create(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateCreate($request);

    $model = new $this->modelClass($inputs);
    DB::transaction(
      fn () => $model->save()
    );
    return  response()->json($this->transformSingleResource($model), 201);
  }

  public function update(Request $request, int $id)
  {
    $this->validateUser();
    $inputs = $this->validateUpdate($request, $id);

    /** @var Model $object */
    $object = $this->baseQuery()->findOrFail($id);
    $object->forceFill($inputs);
    DB::transaction(
      fn () => $object->save()
    );
    return $this->transformSingleResource($object->unsetRelations());
  }

  public function destroy(int $id)
  {
    $this->validateUser();
    $this->validateDelete($id);

    return DB::transaction(
      fn () => $this->baseQuery()->findOrFail($id)->delete()
    );
  }

  protected function baseQuery()
  {
    // standard query
    $query = $this->modelClass::with($this->modelClass::getWithable($this->userType))
      ->orderBy($this->orderAttr, $this->orderDirection);

    return $query;
  }

  protected function filterQuery($query, $param, ?string $value)
  {
    if ($value === null) {
      return $query->whereNull($param);
    }

    $op1 = substr($value, 0, 1);
    $op2 = substr($value, 0, 2);
    $op = $this->opMap[$op2] ?? $this->opMap[$op1] ?? $this->opMap[null];
    if ($op['param_count'] === 1) {
      $opValue = substr($value, $op['op_len']);
      // pattern like 'field_a,field_b,..' are always wild match
      if (strpos($param, ',') > 0) {
        $pattern = '%' . preg_replace('/[, ]+/', '%', $opValue) . '%';
        return $query->whereRaw("concat_ws(' ', ${param}) like '${pattern}'");
      } else {
        $opValue = ($opValue == NULL_STRING) ? null : $opValue;
        return $op['fn']($query, $param, $opValue);
      }
    } else {
      $opValues = explode(',', substr($value, $op['op_len']), 2);
      return $op['fn']($query, $param, $opValues[0], $opValues[1]);
    }
  }

  /**
   * create default query for model (order, rsp )
   */
  protected function standardQuery(array $inputs)
  {
    // base query
    $query = $this->baseQuery();

    // model defined filter
    foreach ($this->modelClass::getFilterable() as $filter => $attr) {
      if (isset($inputs[$filter])) {
        $paramValue = $inputs[$filter];
        $filter = substr($filter, 0, strpos($filter, '-') ?: 100);
        if (is_string($attr)) {
          $query->whereHas($filter, fn ($q) => $this->filterQuery($q, $attr, $paramValue));
        } else {
          $this->filterQuery($query, $filter, $paramValue);
        }
      }
    }

    // search pattern
    if (isset($inputs['search'])) {
      $searchStrings = preg_split('/,/', $inputs['search'], -1, PREG_SPLIT_NO_EMPTY);
      $searchableAttrs = $this->modelClass::getSearchable();
      foreach ($searchStrings as $searchString) {
        // every $searchString must exist
        $pattern = '%' . $searchString . '%';
        $query->where(function ($query) use ($searchableAttrs, $pattern) {
          foreach ($searchableAttrs as $filter => $attr) {
            // in any attributes
            if (is_string($attr)) {
              if (strpos($attr, ',') > 0) {
                $query->orWhereHas($filter, fn ($q) => $q->whereRaw("concat_ws(' ', ${attr}) like '${pattern}'"));
              } else {
                $query->orWhereHas($filter, fn ($q) => $q->where($attr, 'like', $pattern));
              }
            } else {
              $query->orWhere($filter, 'like', $pattern);
            }
          }
        });
      }
    }

    $limit = min($inputs['limit'] ?? PHP_INT_MAX, 200);
    $offset = max($inputs['offset'] ?? 0, 0);

    return $query->limit($limit)->offset($offset);
  }

  /**
   * validate url against rsp
   */
  protected function validateUser()
  {
    $guard = in_array('auth:admin', request()->route()->middleware()) ? 'admin' : 'api';
    $this->user = auth($guard)->user();
    $this->userType = $this->user ? ($this->user->cognito_id ? 'customer' : 'admin') : 'customer';
  }

  protected function getListRules(array $inputs = []): array
  {
    return [];
  }

  protected function getCreateRules(array $inputs = []): array
  {
    return [];
  }

  protected function getUpdateRules(array $inputs = []): array
  {
    return [];
  }

  protected function getDeleteRules(): array
  {
    return [];
  }

  protected function validateList(Request $request): array
  {
    $inputs = $request->all();

    // retrieve rules from extended class
    $rules = $this->getListRules($inputs);

    if ($rules) {
      // append default rules
      foreach ($this->defaultRules as $filter => $rule) {
        $rules[$filter] = $rule;
      }
    }

    return $this->validateRules($inputs, $rules);
  }

  protected function validateCreate(Request $request): array
  {
    $inputs = $request->all();
    return $this->validateRules($inputs, $this->getCreateRules($inputs));
  }

  protected function validateUpdate(Request $request, int $id): array
  {
    if ($this->modelClass::where($this->keyName, $id)->count() <= 0) {
      abort(404, 'The object to be updated does not exist.');
    }

    $inputs = $request->all();
    $inputs = $this->validateRules($inputs, $this->getUpdateRules($inputs));
    if (empty($inputs)) {
      abort(400, 'input data can not be empty.');
    }

    // validate and update attributers
    $updatable = $this->modelClass::getUpdatable($this->userType);
    foreach ($inputs as $attr => $value) {
      if (!in_array($attr, $updatable)) {
        abort(400, 'attribute: [' . $attr . '] is not updatable.');
      }
    }

    return $inputs;
  }

  protected function validateDelete(int $id): array
  {
    if ($this->modelClass::where($this->keyName, $id)->count() <= 0) {
      abort(404, 'The object to be deleted does not exist.');
    }

    return $this->validateRules([$this->keyName => $id], $this->getDeleteRules());
  }

  protected function validateRules(array $inputs, array $rules, $ignoreUndefined = false): array
  {
    if ($rules) {
      if (!$ignoreUndefined) {
        foreach ($inputs as $key => $value) {
          if (!isset($rules[$key]) && $key !== '_') {
            abort(400, 'Parameter: [' . $key . '] is not allowed');
          }
        }
      }

      $inputs = Validator::make($inputs, $rules)->stopOnFirstFailure()->validate();
    }
    return $inputs;
  }
}
