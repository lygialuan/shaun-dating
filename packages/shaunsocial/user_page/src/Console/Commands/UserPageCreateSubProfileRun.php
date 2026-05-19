<?php

namespace Packages\ShaunSocial\UserPage\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Packages\ShaunSocial\UserPage\Models\UserPageCreateSubProfile;
use Packages\ShaunSocial\UserPage\Repositories\Api\UserPageRepository;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Dating\Models\DatingAttributeValue;
use Packages\ShaunSocial\Dating\Models\DatingInterestAttributeValue;

class UserPageCreateSubProfileRun extends Command
{
    protected $signature = 'shaun_user_page:create_sub_profiles';
    protected $description = 'Automatically create sub-profile pages for users';

    protected UserPageRepository $userPageRepository;

    public function __construct(UserPageRepository $userPageRepository)
    {
        parent::__construct();
        $this->userPageRepository = $userPageRepository;
    }

    public function handle(): int
    {
        $this->info('Starting sub-profile page generation...');

        $subProfile = UserPageCreateSubProfile::where('is_created', 0)->first();

        if (! $subProfile) {
            $this->info('No sub-profiles to create.');
            return 0;
        }

        $faker = Faker::create('en_US');

        DB::beginTransaction();
        try {
            $this->createUsersForSubProfile($subProfile, $faker);

            $subProfile->is_created = 1;
            $subProfile->save();

            DB::commit();
            $this->info("Created sub-profile ID: {$subProfile->id}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed sub-profile ID {$subProfile->id}: " . $e->getMessage());
        }

        $this->info('Sub-profile page generation finished.');
        return 0;
    }

    private function createUsersForSubProfile(UserPageCreateSubProfile $subProfile, $faker): void
    {
        $genderName     = $this->getGenderName($subProfile->gender_id);
        $countryName    = $this->getCountryName($subProfile->country_id);
        $aboutTraits    = $this->extractAttributesWithIds($subProfile->about_me, true);
        $interestTraits = $this->extractAttributesWithIds($subProfile->interests, false);
        for ($i = 0; $i < $subProfile->number_of_users; $i++) {
            $firstName = $faker->firstName;
            $lastName  = $faker->lastName;
            $fullName  = "$firstName $lastName";
            $username  = strtolower(Str::slug($fullName . '_' . round(microtime(true) * 1000), '_'));
            $age       = rand($subProfile->from_age, $subProfile->to_age);
            $aboutData = $this->generateAboutText($faker, $fullName, $age, $genderName, $countryName, $aboutTraits, $interestTraits);
            $userData  = [
                'name'                => $fullName,
                'user_name'           => $username,
                'age'                 => $age,
                'role_id'             => $subProfile->expire_role_id,
                'gender_id'           => $subProfile->gender_id,
                'country_id'          => $subProfile->country_id,
                'state_id'            => $subProfile->state_id,
                'city_id'             => $subProfile->city_id,
                'about'               => $aboutData['aboutText'],
                'interest_attributes' => $aboutData['interestIds'], 
                'attributes'          => $aboutData['traitIds'],
            ];

            $this->userPageRepository->store_fake_profile($userData, $subProfile->user_id);
        }
    }

    private function getGenderName(int $genderId): string
    {
        $gender = Gender::findByField('id', $genderId);
        return $gender ? $gender->getTranslatedAttributeValue('name') : 'Not specified';
    }

    private function getCountryName(int $countryId): string
    {
        $country = Country::findByField('id', $countryId);
        return $country ? $country->getTranslatedAttributeValue('name') : 'Unknown';
    }

    private function extractAttributesWithIds(string $jsonAttributes, bool $isAboutMe = true): array
    {
        $decoded = json_decode($jsonAttributes, true) ?? [];
        $flattened = array_merge(...array_values($decoded));

        return collect($flattened)->map(function ($id) use ($isAboutMe) {
            $item = $isAboutMe ? DatingAttributeValue::findByField('id', $id) : DatingInterestAttributeValue::findByField('id', $id);
            if ($item && $item->is_active) {
                return [
                    'id'   => $item->id,
                    'name' => $item->getTranslatedAttributeValue('name'),
                ];
            }

            return null;
        })->filter()->values()->toArray();
    }

   private function generateAboutText($faker, string $name, int $age, string $gender, string $country, array $aboutTraits, array $interests): array
    {
        $randomTraits = collect($aboutTraits)->shuffle()->take(rand(2, 3))->values();
        $traitsText   = $randomTraits->pluck('name')->implode(', ');
        $traitIds     = $randomTraits->pluck('id')->implode(' '); 

        $randomInterests = collect($interests)->shuffle()->take(rand(2, 4))->values();
        $interestsText   = $randomInterests->pluck('name')->implode(', ');
        $interestIds     = $randomInterests->pluck('id')->implode(' ');

        $templates = [
            "Hi, I'm $name, a $age-year-old $gender from $country. I enjoy $interestsText and people often describe me as $traitsText. I love exploring new experiences, meeting interesting people, and learning new things every day.",
            "Hello! My name is $name, $age years old, living in $country. As a $gender, I enjoy $interestsText. Friends usually say I'm $traitsText. I value meaningful conversations and try to make the most out of life.",
            "Hey there! I'm $name, a $age-year-old $gender from $country. My passions include $interestsText and I'm often considered $traitsText. I enjoy discovering new places, connecting with people, and embracing life's adventures.",
            "$name here, $age-year-old $gender living in $country. I love $interestsText and friends describe me as $traitsText. Life is an adventure, and I enjoy every moment of it with positivity and curiosity.",
            "Hi! I'm $name, $age, from $country. I enjoy $interestsText and people see me as $traitsText. Exploring new cultures, trying out new hobbies, and meeting new friends excites me.",
            "Hello! I'm $name, a $age-year-old $gender from $country. My hobbies include $interestsText. People often call me $traitsText. I strive to live fully, enjoy learning, and embrace new experiences.",
            "Hey! I'm $name from $country, $age years old. I enjoy $interestsText and consider myself $traitsText. I like spending time outdoors, meeting new people, and sharing good vibes.",
            "Hi, I'm $name, $age-year-old $gender from $country. $interestsText keep me inspired and motivated, and friends say I'm $traitsText. I love exploring the unknown and making each day memorable.",
            "Hello! I'm $name, $age years old, a $gender from $country. I enjoy $interestsText and people often describe me as $traitsText. Life is about curiosity, fun, and meaningful connections.",
            "Hey there! I'm $name, $age, living in $country. I enjoy $interestsText and friends call me $traitsText. I try to make life joyful, learn continuously, and embrace new opportunities.",
            "$name here, $age-year-old $gender from $country. I love $interestsText and people describe me as $traitsText. I enjoy meeting people, exploring new hobbies, and challenging myself.",
            "Hi! I'm $name, $age years old, a $gender from $country. $interestsText are some of my passions, and I'm often called $traitsText. I like living intentionally and experiencing life fully.",
            "Hello! I'm $name from $country, $age years old. I enjoy $interestsText and friends say I'm $traitsText. Life is a journey, and I love exploring it with curiosity and enthusiasm.",
            "Hey! I'm $name, a $gender from $country, aged $age. I enjoy $interestsText and am considered $traitsText. I love learning new things, connecting with people, and seeking new experiences.",
            "Hi, I'm $name from $country, $age years old. I enjoy $interestsText and people often describe me as $traitsText. I like to spend my time exploring hobbies, meeting new people, and enjoying life's moments.",
            "Hello! I'm $name, $age-year-old $gender living in $country. $interestsText are my passions and people call me $traitsText. I enjoy connecting with others and making every day count.",
            "Hey! I'm $name, $age, from $country. I enjoy $interestsText and friends describe me as $traitsText. Life is full of opportunities, and I love embracing them with positivity and curiosity.",
            "Hi, my name is $name. I'm $age-year-old $gender from $country. I enjoy $interestsText and people say I'm $traitsText. I try to make the most of every day by learning, exploring, and connecting with others.",
            "Hello! I'm $name, $age years old, a $gender from $country. I love $interestsText and friends see me as $traitsText. Life is an adventure, and I enjoy discovering new places and meeting new people.",
            "Hey! I'm $name from $country, $age-year-old $gender. I enjoy $interestsText and people describe me as $traitsText. I like exploring new experiences, sharing laughs, and making memories that last."
        ];

        return [
            'aboutText'   => $faker->randomElement($templates),
            'interestIds' => $interestIds, 
            'traitIds'    => $traitIds,    
        ];
    }
}