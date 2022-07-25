<?php

namespace App\Models;

use Carbon\Carbon;

define('USER_TYPE', [
  'admin'     => 0b0_1_0,
  'customer'  => 0b0_0_1,
]);

/**
 *
 */
trait TraitModel
{
  /**
   * internal cache data
   */
  static protected $filterable = null;
  static protected $searchable = null;
  static protected $lite = null;
  static protected $updatable = ['admin' => null, 'customer' => null];
  static protected $listable = ['admin' => null, 'customer' => null];

  /**
   * overidable attributes
   */

  // attribute options
  static protected $attributesOption = [];
  // [
  //   'attribute_name' => [
  //      'filterable' => 0|1|nested_field,
  //      'searchable' => 0|1|nested_field,
  //      'lite' => 0|1,
  //      'updatable' => 0b0_a_c,
  //      'listable' => 0b0_a_c,
  // ];
  /**
   * exlaination
   *    filterable:  attribute can be used in url parameter, e.g http://hostname/path?attribute_name='xxx'
   *    filterable:  attribute can be used in url search parameter, e.g http://hostname/path?search='xxx'
   *    lite:        attribute appears when object is retrieved via API as a nested object
   *    updatable:   attribute can be updated via API
   *    listable:    attribute appears when object is retrieved via API
   *
   * for updatable & listable
   *    a: 0|1, for admin
   *    b: 0|1, for customer
   */
  static protected $withAttrs = [];

  /**
   * public data, used when 'status' exist and changed
   */
  protected bool $statusChanged = false;

  static protected function getAttributesOption()
  {
    return static::$attributesOption;
  }

  /**
   * retrieve boolean attributes in attributesOption
   * @param string $name attribute type: 'lite'
   */
  static protected function getBooleanAttributes(string $name)
  {
    if (static::$$name !== null) {
      return static::$$name;
    }

    $result = [];
    foreach (static::$attributesOption ?: [] as $key => $attribute) {
      if ($attribute[$name]) {
        $result[] = $key;
      }
    };
    return static::$$name = $result;
  }

  /**
   * retrieve boolean attributes in attributesOption
   * @param string $name attribute type: 'fillable'
   */
  static protected function getMixedAttributes(string $name)
  {
    if (static::$$name !== null) {
      return static::$$name;
    }

    $result = [];
    foreach (static::$attributesOption ?: [] as $key => $attribute) {
      if ($attribute[$name]) {
        $result[$key] = $attribute[$name];
      }
    };
    return static::$$name = $result;
  }

  /**
   * retrieve user type based attributes in attributesOption
   * @param string $name attribute type: 'updatable', 'listable'
   */
  static protected function getUserTypedAttributes($name, $userType)
  {
    if (static::$$name[$userType] !== null) {
      return static::$$name[$userType];
    }

    $result = [];
    foreach (static::$attributesOption ?: [] as $key => $attribute) {
      if (USER_TYPE[$userType] & $attribute[$name]) {
        $result[] = $key;
      }
    };
    return static::$$name[$userType] = $result;
  }

  static public function getFilterable()
  {
    return static::getMixedAttributes('filterable');
  }

  static public function getSearchable()
  {
    return static::getMixedAttributes('searchable');
  }

  static public function getLite()
  {
    return static::getBooleanAttributes('lite');
  }

  static public function getWithable(string $userType)
  {
    $result = [];
    foreach (static::$withAttrs ?? [] as $attr) {
      if (USER_TYPE[$userType] & (static::$attributesOption[$attr]['listable'] ?? 0)) {
        $result[] = $attr;
      }
    }
    return $result;
  }

  static public function getUpdatable(string $userType)
  {
    return static::getUserTypedAttributes('updatable', $userType);
  }

  static public function getListable(string $userType)
  {
    return static::getUserTypedAttributes('listable', $userType);
  }

  protected function toResourceByAttrs(array $attrs)
  {
    $attributes = [];
    foreach ($attrs as $attr) {
      if (is_object($this->$attr) && method_exists($this->$attr, 'toLite')) {
        $attributes[$attr] = $this->$attr->toLite();
      } else if ($this->$attr instanceof Carbon) {
        // use raw value to decide it's a date of datetime
        $attributes[$attr] =  strpos($this->getAttributes()[$attr], ' ') !== false ?
          $this->$attr->format('Y-m-d H:i:s') :
          $this->$attr->format('Y-m-d');
      } else {
        $attributes[$attr] = $this->$attr;
      }
    }
    return $attributes;
  }

  public function toResource($userType)
  {
    // default behavior
    if (static::$attributesOption === null) {
      return $this->toArray();
    }

    return $this->toResourceByAttrs(static::getListable($userType));
  }

  public function toLite()
  {
    // default behavior
    if (static::$attributesOption === null) {
      return $this->toArray();
    }
    return $this->toResourceByAttrs(static::getLite());
  }

  public function save(array $options = [])
  {
    if (!$this->exists) {
      $before = 'callBeforeCreate';
      $after = 'callAfterCreate';
    } else {
      $before = 'callBeforeUpdate';
      $after = 'callAfterUpdate';
    }

    $this->$before();
    $this->statusChanged = $this->isDirty('status');
    $result = parent::save($options);
    if ($result) {
      $this->$after();
    }
    $this->statusChanged = false;

    return $result;
  }

  private function callBeforeCreate()
  {
    if (method_exists($this, 'beforeCreate')) {
      $this->beforeCreate();
    } else if (method_exists($this, 'beforeSave')) {
      $this->beforeSave();
    }
  }

  private function callAfterCreate()
  {
    if (method_exists($this, 'afterCreate')) {
      $this->afterCreate();
    } else if (method_exists($this, 'afterSave')) {
      $this->afterSave();
    }
  }

  private function callBeforeUpdate()
  {
    if (method_exists($this, 'beforeUpdate')) {
      $this->beforeUpdate();
    } else if (method_exists($this, 'beforeSave')) {
      $this->beforeSave();
    }
  }

  private function callAfterUpdate()
  {
    if (method_exists($this, 'afterUpdate')) {
      $this->afterUpdate();
    } else if (method_exists($this, 'afterSave')) {
      $this->afterSave();
    }
  }

  public function delete()
  {
    $this->callBeforeDelete();
    if ($result = parent::delete()) {
      $this->callAfterDelete();
    }
    return $result;
  }

  protected function callBeforeDelete()
  {
    if (method_exists($this, 'beforeDelete')) {
      $this->beforeDelete();
    }
  }

  protected function callAfterDelete()
  {
    if (method_exists($this, 'afterDelete')) {
      $this->afterDelete();
    }
  }

  protected function isFloatDirty($floatAttributes)
  {
    $attrs = is_array($floatAttributes) ? $floatAttributes : [$floatAttributes];
    foreach ($attrs as $attr) {
      if (abs($this->getRawOriginal($attr) - $this->getAttributes()[$attr]) > 0.000001) {
        return true;
      }
    }

    return false;
  }
}
