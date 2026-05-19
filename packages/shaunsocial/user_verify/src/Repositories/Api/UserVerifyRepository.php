<?php

namespace Packages\ShaunSocial\UserVerify\Repositories\Api;

use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\UserVerify\Http\Resources\UserVerifyFileResource;
use Packages\ShaunSocial\UserVerify\Models\UserVerifyFile;
use Packages\ShaunSocial\UserVerify\Notification\UserVerifyRequestSendNotification;

class UserVerifyRepository
{
    public function get_files($viewer)
    {        
        return UserVerifyFileResource::collection($viewer->getVerifyFiles());
    }

    public function upload_file($file, $viewerId)
    {
        $storageFile = File::store($file, [
            'parent_type' => 'user_verify_file',
            'user_id' => $viewerId,
            'extension' => $file->getClientOriginalExtension(),
			'name' => $file->getClientOriginalName()
        ]);

        $userVerifyFile = UserVerifyFile::create([
            'user_id' => $viewerId,
            'file_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $userVerifyFile->id,
        ]);

        return new UserVerifyFileResource($userVerifyFile);
    }

    public function delete_file($id)
    {
        $file = UserVerifyFile::findByField('id', $id);
        $file->delete();
    }

    public function store_request($files, $viewer)
    {
        foreach ($files as $fileId) {
            $file = UserVerifyFile::findByField('id', $fileId);
            $file->update([
                'is_accept' => true
            ]);
        }

        $viewer->update([
            'verify_status_at' => now(),
            'verify_status' => UserVerifyStatus::SENT
        ]);

        //push notify to admin
        $admin = User::findByField('id', config('shaun_core.core.user_root_id'));
        UserNotification::deleteFromAndSubject($viewer, $admin, $viewer);
        Notification::send($admin, $viewer, UserVerifyRequestSendNotification::class, $viewer, [], 'shaun_user_verify', false);
    }
}
