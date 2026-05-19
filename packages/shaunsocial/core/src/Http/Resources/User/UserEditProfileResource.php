<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Utility\GenderResource;
use Packages\ShaunSocial\Core\Models\Gender;

class UserEditProfileResource extends JsonResource
{
    protected $preserveKeys = true;
    
    public function toArray($request)
    {        
        return [
            'name' => $this->getName(),
            'birthday' => $this->birthday,
            'links' => $this->getLinks(),
            'address' => $this->location,
            'address_full' => $this->getAddessFull(),
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'zip_code' => $this->zip_code,
            'bio' => $this->bio,
            'about' => $this->about,
            'gender_id' => $this->gender_id,
            'genders' => GenderResource::collection(Gender::getAll()),
            'avatar' => $this->getAvatar(),
            'timezone' => $this->timezone,
            'timezones' => getTimezoneList(),
            'privacyField' => $this->getPrivacyFieldSetting()
        ];
    }
}
