<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\MailUnsubscribe;
use Packages\ShaunSocial\Core\Models\TranslateProvider;
use Packages\ShaunSocial\Core\Models\UserFcmToken;
use Packages\ShaunSocial\Core\Support\Facades\Mail;

class UtilityRepository
{
    public function share_email($data, $viewer)
    {
        $emails = $data['emails'];
        $emails = Str::of($emails)->explode(',')->toArray();
        $subject = findByTypeId($data['subject_type'], $data['subject_id']);
        
        foreach ($emails as $email) {
            $email = trim($email);
            //Send email
            Mail::send('share_email', $email, [
                'sender_title' => $viewer->getName(),
                'message' => $data['message'] ? $data['message'] : '' ,
                'link' => $subject->getHref()
            ]);
        }
    }

    public function unsubscribe_email($email)
    {
        MailUnsubscribe::create([
            'email' => $email
        ]);
    }

    public function store_contact($data)
    {
        Mail::send('contact', setting('site.email'), [
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message']
        ]);
    }

    public function store_fcm_token($data, $viewer)
    {
        $tokens = $viewer->getFcmTokens();

        $token = $tokens->first(function ($value, $key)  use ($data) {
            return $value->token == $data['token'];
        });

        if (! $token) {
            //Delete old token
            $tokenOld = UserFcmToken::findByField('hash', md5($data['token']));
            if ($tokenOld) {
                $tokenOld->delete();
            }

            UserFcmToken::create([
                'user_id' => $viewer->id,
                'type' => $data['type'],
                'token' => $data['token']
            ]);
        }
    }

    public function remove_web_fcm_token($token, $viewer)
    {
        $tokens = $viewer->getFcmTokens();

        $fcmToken = $tokens->first(function ($value, $key)  use ($token) {
            return $value->token == $token;
        });
        
        if ($fcmToken && $fcmToken->type == 'web') {
            $fcmToken->delete();
        }
    }

    public function content_translate($subjectType, $subjectId, $field, $language)
    {
        $subject = findByTypeId($subjectType, $subjectId);
        $content = $subject->getContentForTranslate($field);

        $result = TranslateProvider::getDefault()->translate($content, $language);

        return $result;
    }
}
