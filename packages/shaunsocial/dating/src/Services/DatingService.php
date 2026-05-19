<?php

namespace Packages\ShaunSocial\Dating\Services;

use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\State;
use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\Dating\Models\DatingSwipeMatch;
use Packages\ShaunSocial\Dating\Models\DatingSwipe;
use Packages\ShaunSocial\Dating\Models\DatingProfileCompletionSetting;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Dating\Repositories\Api\DatingRepository;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Dating\Notification\DatingReminderAdminReviewPhotoNotification;

class DatingService {
    protected $datingRepository;

    public function __construct(DatingRepository $datingRepository)
    {
        $this->datingRepository = $datingRepository;
    }

    public function makeAddress($countryId, $stateId, $cityId) {
        $country = Country::findByField('id', $countryId);
        $state = State::findByField('id', $stateId);
        $city = City::findByField('id', $cityId);

        $addressSearch = implode(', ', array_filter([
            $city ? $city->name : null,
            $state ? $state->name : null,
            $country ? $country->name : null,
        ]));
        
        return $addressSearch;
    }

    public static function generateCacheKeyGetUser(array $data): string
    {
        $parts = ['dating_users'];

        $parts[] = 'page_' . ((int) ($data['page'] ?? 1));
        $parts[] = 'gender_' . ((int) ($data['gender'] ?? 0));
        $parts[] = 'ageMin_' . ((int) ($data['ageMin'] ?? 0));
        $parts[] = 'ageMax_' . ((int) ($data['ageMax'] ?? 0));
        $parts[] = 'verified_' . (($data['verifiedProfiles'] ?? 'all') === 'show' ? 1 : 0);

        if (!empty($data['attributeValues'])) {
            $attrs = array_map('intval', explode(',', $data['attributeValues']));
            $parts[] = 'attr_' . md5(implode(',', $attrs));
        }

        if (!empty($data['interestAttributeValues'])) {
            $interests = array_map('intval', explode(',', $data['interestAttributeValues']));
            $parts[] = 'interest_' . md5(implode(',', $interests));
        }

        if (!empty($data['locationId'])) {
            $parts[] = 'location_id_' . (int) $data['locationId'];
        }

        if (!empty($data['locationText'])) {
            $text = mb_strtolower(trim($data['locationText']));
            $parts[] = 'location_text_' . md5($text);
        }

        return implode('.', $parts);
    }

    public function checkMatch($userId, $targetId)
    {
        $likedBack = DatingSwipe::where('user_id', $targetId)->where('target_user_id', $userId)->where('action', 'like')->exists();

        if (!$likedBack) return false;

        $ids = collect([$userId, $targetId])->sort()->values();

        DatingSwipeMatch::firstOrCreate([
            'user_one_id' => $ids[0],
            'user_two_id' => $ids[1],
        ]);
        
        return true;
    }

    function makeClientId(string $prefix = 'msg'): string
    {
        return $prefix . '_' . now()->timestamp . '_' . Str::uuid();
    }

    /**
     * Calculate profile completion percent
     */
    public function calculateProfileCompletion(User $user): int
    {
        $setting = DatingProfileCompletionSetting::findByField('id', 1);

        if (!$setting || !$setting->is_active) {
            return 0;
        }

        $rules = [
            'basic_info'           => 'hasBasicInfo',
            'about'                => 'hasAbout',
            'profile_verification' => 'isVerified',
            'work_education'       => 'hasWorkEducation',
            'more_about'           => 'hasMoreAbout',
            'interests'            => 'hasInterests',
            'social_profiles'      => 'hasSocialProfiles',
        ];

        $score = 0;

        foreach ($rules as $field => $method) {
            $value = (int) $setting->{$field};

            if ($value > 0 && $this->{$method}($user)) {
                $score += $value;
            }
        }

        return min($score, 100);
    }

    /* ================= RULES ================= */
    protected function hasBasicInfo(User $user): bool
    {
        return (int) $user->gender_id > 0 && !is_null($user->birthday) && (int) $user->dating_addresses_id > 0;
    }

    protected function hasAbout(User $user): bool
    {
        return filled(trim($user->about));
    }

    protected function isVerified(User $user): bool
    {
        return $user->verify_status == UserVerifyStatus::OK;
    }

    protected function hasWorkEducation(User $user): bool
    {
        return filled($user->job_title) && filled($user->school_name) && filled($user->company_name);
    }

    protected function hasMoreAbout(User $user): bool
    {
        return filled($user->attributes);
    }

    protected function hasInterests(User $user): bool
    {
        return filled($user->interest_attributes);
    }

    protected function hasSocialProfiles(User $user): bool
    {
        return filled($user->links);
    }
    /* ================= END RULES ================= */

    /**
     * Notification will send to admin ( all admin ) daily. It will send if the list has new uploaded photo.
     */
    public function reminderAdminReviewPhotos()
    {
        if (!$this->datingRepository->hasPendingReviewPhotos()) return;

        $this->datingRepository->queryAdminsCanManageProfilePictures("dating.manage_profile_pictures")->chunkById(1000, function ($admins) {
            foreach ($admins as $admin) {
                Notification::send($admin, $admin, DatingReminderAdminReviewPhotoNotification::class, '', ['is_system' => true], 'shaun_dating', false);
            }
        });
    }
}