<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Subscription;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Plan
 * 
 * @property int $id
 * @property string $name
 * @property string $catagory
 * @property string $description
 * @property int $subscription_level
 * @property string $contract_term
 * @property array $price
 * @property bool $auto_renew
 * @property string|null $url
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Subscription[] $subscriptions
 *
 * @package App\Models\Base
 */
class Plan extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'plans';

  protected $casts = [
    'subscription_level' => 'int',
    'price' => 'json',
    'auto_renew' => 'bool'
  ];

  protected $fillable = [
    'name',
    'catagory',
    'description',
    'subscription_level',
    'contract_term',
    'price',
    'auto_renew',
    'url',
    'status'
  ];

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }
}
