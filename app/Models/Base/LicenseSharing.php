<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LicenseSharingInvitation;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LicenseSharing
 * 
 * @property int $id
 * @property int $user_id
 * @property int $subscription_id
 * @property string $product_name
 * @property int $subscription_level
 * @property int $total_count
 * @property int $free_count
 * @property int $used_count
 * @property string $status
 * @property array|null $logs
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Product $product
 * @property Subscription $subscription
 * @property User $user
 * @property Collection|LicenseSharingInvitation[] $license_sharing_invitations
 *
 * @package App\Models\Base
 */
class LicenseSharing extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'license_sharings';

  protected $casts = [
    'user_id' => 'int',
    'subscription_id' => 'int',
    'subscription_level' => 'int',
    'total_count' => 'int',
    'free_count' => 'int',
    'used_count' => 'int',
    'logs' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'subscription_id',
    'product_name',
    'subscription_level',
    'total_count',
    'free_count',
    'used_count',
    'status',
    'logs'
  ];

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_name', 'name');
  }

  public function subscription()
  {
    return $this->belongsTo(Subscription::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function license_sharing_invitations()
  {
    return $this->hasMany(LicenseSharingInvitation::class);
  }
}
