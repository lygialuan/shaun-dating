<?php

namespace Packages\ShaunSocial\MigrateOldDating\Console\Commands;

use Throwable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Dating\Models\DatingInterestAttributeValue;
use Packages\ShaunSocial\Dating\Models\DatingAddress;
use Packages\ShaunSocial\MigrateOldDating\Models\SyncOldUser;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Carbon\Carbon;

class SyncOldUserRun extends Command
{
    protected $signature = 'shaun_migrate_old_dating:sync_old_users';
    protected $description = 'Sync users from old database';
    protected $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    public function handle()
    {
        $job = $this->initJob();
        
        if (!$job || $job->status === 'done') return;

        try {
            $users = $this->getUsers($job->last_id);

            if ($users->isEmpty()) {
                $job->update(['status' => 'done']);
                return;
            }

            foreach ($users as $user) {
                $this->processUser($user, $job);
            }

            $job->save();
        } catch (Throwable $e) {
            Log::error('Sync job failed', ['error' => $e->getMessage()]);
        }
    }

    protected function initJob()
    {
        $job = SyncOldUser::where('status', 'processing')->first();

        if (!$job) return null;

        try {
            config([
                'database.connections.old_db' => [
                    'driver' => 'mysql',
                    'host' => $job->database_host,
                    'port' => $job->port,
                    'database' => $job->database_name,
                    'username' => $job->user_name,
                    'password' => $job->password,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ]
            ]);

            DB::connection('old_db')->getPdo(); 

            return $job;
        } catch (Throwable $e) {
            Log::error('Old DB connect failed', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    protected function getUsers($lastId)
    {
        return DB::connection('old_db')->table('users')->where('id', '>', (int) $lastId)->orderBy('id')->limit(5)->get();
    }

    protected function processUser($user, $job)
    {
        if (User::where('email', $user->email)->exists()) {
            $job->last_id = $user->id;
            return;
        }

        $viewer = User::create($this->buildUserData($user));

        $this->syncUserMedia($viewer, $user);

        $job->last_id = $user->id;
        
        $job->increment('total'); 
    }

    protected function syncUserMedia($viewer, $user)
    {
        $media = $this->buildUserMedia($user);

        foreach ($media as $index => $item) {
            $this->handleFile($viewer, $item['url'], $index, $item['is_avatar']);
        }
    }

    protected function buildUserMedia($user)
    {
        $photos = $this->getPhotos($user->id)->map(fn($p) => ['url' => $this->buildThumbnailPath($p), 'is_avatar' => false])->toArray();
        
        if ($user->avatar) {
            array_unshift($photos, ['url' =>  env('URL_SITE') . "/uploads/users/avatar/$user->id/" . $user->avatar, 'is_avatar' => true]);
        }

        return $photos;
    }

    protected function getPhotos($userId)
    {
        return DB::connection('old_db')->table('photos')->where('user_id', $userId)->where('type', 'Photo_Album')->where('ordering','>', 0)->orderBy('ordering')->select('id', 'created', 'thumbnail')->limit(setting('feature.limit_photos_verify'))->get();
    }

    protected function buildThumbnailPath($photo)
    {
        $date = Carbon::parse($photo->created);

        return sprintf(env('URL_SITE').'/uploads/photos/thumbnail/%s/%s/%s/%s/%s', $date->format('Y'), $date->format('m'), $date->format('d'), $photo->id, $photo->thumbnail);
    }

    protected function buildUserData($user)
    {
        return array_merge($this->mapUser($user),['interest_attributes' => $this->getInterest($user->id)], $this->getAddress($user->id));
    }

    protected function mapUser($user)
    {
        return [
            'name'           => $user->name,
            'email'          => $user->email,
            'user_name'      => Str::slug(Str::before($user->email, '@')) . '_' . $user->id,
            'gender_id'      => $this->mapGender($user->gender),
            'birthday'       => $user->birthday,
            'is_active'      => $user->active,
            'about'          => $user->about,
            'password'       => Hash::make(Str::random(10)),
            'email_verified' => true,
            'has_email'      => true,
        ];
    }

    protected function mapGender($gender)
    {
        return match (strtolower(trim($gender))) {
            'male'   => 1,
            'female' => 2,
            default  => $gender,
        };
    }

    protected function getInterest($userId)
    {
        $ids = DB::connection('old_db')->table('user_interest_values')->where('user_id', $userId)->distinct()->pluck('interest_id');

        if ($ids->isEmpty()) return null;

        $names = DB::connection('old_db')->table('user_interests')->whereIn('id', $ids)->pluck('name');

        return DatingInterestAttributeValue::whereIn('name', $names)->pluck('id')->implode(' ');
    }

    protected function getAddress($userId)
    {
        $country = DB::connection('old_db')->table('user_countries')->where('user_id', $userId)->first();

        if (!$country) return [];

        $address = DatingAddress::findAddress($country->country_id, $country->state_id, $country->city_id);

        return [
            'dating_addresses_id' => $address?->id ?? 0,
            'country_id'          => $country->country_id ?? 0,
            'state_id'            => $country->state_id ?? 0,
            'city_id'             => $country->city_id ?? 0,
        ];
    }

    protected function handleFile($viewer, $url, $position = 0, $isAvatar = false)
    {
        if (!$url) return;

        $tmpPath = null;

        try {
            $response = Http::timeout(10)->retry(1, 100)->get($url);

            if (!$response->successful()) return;

            if (strlen($response->body()) > 1024 * 1024) return;

            $tmpPath = tempnam(sys_get_temp_dir(), 'avatar_');

            file_put_contents($tmpPath, $response->body());

            $file = new UploadedFile($tmpPath, basename(parse_url($url, PHP_URL_PATH)), $response->header('Content-Type'), null, true);

            $this->userRepository->upload_photos_verify($file, $viewer, $position, $isAvatar);

            $viewer->update(['photos_verified' => true, 'dating_addresses_fulltext' => $viewer->getAddessFull() ?? null]);
        } catch (Throwable $e) {
            Log::warning('File sync failed', ['user_id' => $viewer->id, 'url' => $url, 'error' => $e->getMessage()]);
        } finally {
            if ($tmpPath && file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }
}