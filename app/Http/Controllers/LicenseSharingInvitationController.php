<?php

namespace App\Http\Controllers;

use App\Models\LicenseSharing;
use App\Models\LicenseSharingInvitation;
use App\Models\User;
use App\Services\LicenseSharing\LicenseSharingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LicenseSharingInvitationController extends SimpleController
{
  protected string $modelClass = LicenseSharingInvitation::class;

  public function __construct(protected LicenseSharingService $service)
  {
    parent::__construct();
  }

  protected function getGuestListRules(array $inputs = []): array
  {
    return [
      'owner_email'     => ['filled'],
      'status'          => ['filled'],
    ];
  }

  protected function getGuestUpdateRules(array $inputs = []): array
  {
    return [
      'status'          => ['required', Rule::in(
        [
          LicenseSharingInvitation::STATUS_ACCEPTED,
          LicenseSharingInvitation::STATUS_CANCELLED
        ]
      )],
    ];
  }

  protected function getOwnerListRules(array $inputs = []): array
  {
    return [
      'guest_email'     => ['filled'],
      'status'          => ['filled'],
    ];
  }

  protected function getOwnerCreateRules(array $inputs = []): array
  {
    return [
      'guest_email'     => ['required', 'email'],
      'expires_at'      => ['filled', 'date', 'after:today'],
    ];
  }

  protected function getOwnerUpdateRules(array $inputs = []): array
  {
    return [
      'expires_at'      => ['filled', 'date', 'after:today'],
      'status'          => ['filled', Rule::in([LicenseSharingInvitation::STATUS_REVOKED])],
    ];
  }

  protected function guestQuery(array $inputs = [])
  {
    // only open and accepted invitations are visible to guest
    return $this->standardQuery($inputs)
      ->where('guest_id', $this->user->id)
      ->whereIn('status', [
        LicenseSharingInvitation::STATUS_OPEN,
        LicenseSharingInvitation::STATUS_ACCEPTED,
      ]);
  }

  protected function ownerQuery(array $inputs = [])
  {
    // only invitations belong to active license sharing and are not deleted are visible to owner
    return $this->standardQuery($inputs)
      ->where('owner_id', $this->user->id)
      ->where('status', '!=', LicenseSharingInvitation::STATUS_DELETED)
      ->whereHas('license_sharing', function ($query) {
        $query->where('status', LicenseSharing::STATUS_ACTIVE);
      });
  }

  /**
   * get /account/license-sharing-invitations-to-me
   */
  public function accountListToMe(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate($this->getGuestListRules());

    $objects = $this->guestQuery($inputs)->get();
    return ['data' => $this->transformMultipleResources($objects)];
  }

  /**
   * get /account/license-sharing-invitations-to-me/{id}
   */
  public function accountGetToMe(Request $request, int $id)
  {
    $this->validateUser();

    $object = $this->guestQuery()->findOrFail($id);
    return $this->transformSingleResource($object);
  }

  /**
   * update /account/license-sharing-invitations-to-me/{id}
   */
  public function accountUpdateToMe(Request $request, int $id)
  {
    $this->validateUser();
    $inputs = $request->validate($this->getGuestUpdateRules());

    /** @var LicenseSharingInvitation $invitation */
    $invitation = $this->guestQuery()->findOrFail($id);
    if ($inputs['status'] === LicenseSharingInvitation::STATUS_ACCEPTED) {
      // accept invitation
      if ($invitation->status === LicenseSharingInvitation::STATUS_OPEN) {
        $this->service->acceptLicenseSharingInvitation($invitation);
      } else {
        return response()->json(['message' => 'Invitation can not be accepted'], 400);
      }
    } else if ($inputs['status'] === LicenseSharingInvitation::STATUS_CANCELLED) {
      // cancel invitation
      if (in_array($invitation->status, [LicenseSharingInvitation::STATUS_OPEN, LicenseSharingInvitation::STATUS_ACCEPTED])) {
        $this->service->cancelLicenseSharingInvitation($invitation);
      } else {
        return response()->json(['message' => 'Invitation can not be cancelled'], 400);
      }
    } else {
      return response()->json(['message' => 'operation can not execute!'], 400);
    }

    return $this->transformSingleResource($invitation);
  }

  /**
   * get /account/license-sharing-invitations
   */
  public function accountList(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate($this->getOwnerListRules());
    $inputs['owner_id'] = $this->user->id;
    $objects = $this->ownerQuery($inputs)->get();
    return ['data' => $this->transformMultipleResources($objects)];
  }

  /**
   * get /account/license-sharing-invitations/{id}
   */
  public function accountGet(Request $request, int $id)
  {
    $this->validateUser();
    $object = $this->ownerQuery()->findOrFail($id);
    return $this->transformSingleResource($object);
  }

  /**
   * post /account/license-sharing-invitations
   */
  public function accountCreate(Request $request)
  {
    $this->validateUser();
    $inputs = $request->validate($this->getOwnerCreateRules());

    $licenseSharing = $this->user->getActiveLicenseSharing();
    if (!$licenseSharing) {
      return response()->json(['message' => 'You don\'t have licenses to share'], 400);
    }

    $invitationExists = $licenseSharing
      ->active_license_sharing_invitations()
      ->where('guest_email', $inputs['guest_email'])
      ->count() > 0;
    if ($invitationExists) {
      return response()->json(['message' => 'Invitation already exists'], 400);
    }

    /** @var ?User $guest */
    $guest = User::where('email', $inputs['guest_email'])->first();
    if (!$guest) {
      return response()->json(['message' => 'User not found.'], 400);
    }
    if ($guest->id === $this->user->id) {
      return response()->json(['message' => 'You can not share license with yourself.'], 400);
    }
    if ($guest->subscription_level > 1) {
      return response()->json(['message' => 'You can only share license with users who do not have a pro license.'], 400);
    }

    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, $inputs['expires_at'] ?? null);

    return response()->json($this->transformSingleResource($invitation), 201);
  }

  /**
   * post /account/license-sharing-invitations/{id}
   */
  public function accountUpdate(Request $request, int $id)
  {
    $this->validateUser();

    /** @var LicenseSharingInvitation $invitation */
    $invitation = $this->ownerQuery()->findOrFail($id);
    $inputs = $request->validate($this->getOwnerUpdateRules());

    // expires_at and status are exclusive
    if (isset($inputs['expires_at']) && isset($inputs['status'])) {
      return response()->json(['message' => 'Invalid request'], 400);
    }

    if (isset($inputs['expires_at'])) {
      if (!in_array($invitation->status, [LicenseSharingInvitation::STATUS_ACCEPTED, LicenseSharingInvitation::STATUS_OPEN])) {
        return response()->json(['message' => 'Sharing exipirs date can not be updated'], 400);
      }

      $invitation->expires_at = $inputs['expires_at'];
      $invitation->save();
    } else if (isset($inputs['status'])) {
      if ($inputs['status'] !== LicenseSharingInvitation::STATUS_REVOKED) {
        return response()->json(['message' => 'Invalid status'], 400);
      }

      $this->service->revokeLicenseSharingInvitation($invitation);
    } else {
      return response()->json(['message' => 'Invalid request'], 400);
    }

    return $this->transformSingleResource($invitation);
  }



  /**
   * delete /license-sharing-invitations/{id}
   */
  public function accountDelete(int $id)
  {
    $this->validateUser();

    /** @var LicenseSharingInvitation $invitation */
    $invitation = $this->ownerQuery()->findOrFail($id);
    $this->service->deleteLicenseSharingInvitation($invitation);

    return response(status: 204);
  }
}
