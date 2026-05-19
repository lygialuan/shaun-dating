<?php


namespace Packages\ShaunSocial\Core\Services;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail as MailManager;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Jobs\SendMailJob;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\MailRecipient;
use Packages\ShaunSocial\Core\Models\MailTemplate;
use Packages\ShaunSocial\Core\Models\MailUnsubscribe;
use Packages\ShaunSocial\Core\Models\User;

class Mail
{
    public function send($type, $to, $params = [])
    {
        $mailTemplate = MailTemplate::findByField('type', $type);
        
        if (! $mailTemplate) {
            return;
        }

        if (config('shaun_core.core.queue')) {
            $queue = config('shaun_core.queue.mail_normal');
            if (! empty($params['mail_many'])) {
                $queue = config('shaun_core.queue.mail_normal');
            }
            dispatch((new SendMailJob($type, $to, $params))->onQueue($queue));

            return;
        }

        if (! empty($params['mail_many']) || setting('mail.queue')) {            
            MailRecipient::create([
                'type' => $type,
                'to' => is_object($to) ? $to->email : $to,
                'params' => json_encode($params),
            ]);

            return;
        }

        $this->sendRow($type, $to, $params);
    }

    public function sendRow($type, $to, $params = [])
    {
        $mailTemplate = MailTemplate::findByField('type', $type);

        if (! $mailTemplate) {
            return;
        }

        $language = isset($params['language']) ? $params['language'] : '';
        $params['site_name'] = setting('site.title');

        if (is_string($to)) {
            $user = User::findByField('email', $to);
            if ($user) {
                $to = $user;
            }
        } elseif (is_numeric($to)) {
            $user = User::findByField('id', $to);
            if (! $user) {
                return;
            }

            $to = $user;
        }

        if (! $language) {
            if (is_object($to)) {
                $language = getUserLanguage($to);
            }
        }
        $languages = Language::getAll();
        if (! $language) {
            $defaultLanguage = $languages->first(function ($value, $key) {
                return $value->is_default;
            });
            
            $language = $defaultLanguage->key;
        }

        if (! in_array($language, $languages->pluck('key')->toArray())) {
            $language = config('shaun_core.language.system_default');
        }

        $typeIgnoreList = config('shaun_core.mail.type_ignore_unsubscribe_list');

        $isUser = false;

        if (is_object($to)) {
            if (! $to->id || ! $to->has_email) {
                return;
            }
            $isUser = true;
        }

        $subjectTemplate = $mailTemplate->getTranslatedAttributeValue('subject', $language);
        $contentTemplate = $mailTemplate->getTranslatedAttributeValue('content', $language);

        if ($isUser) {
            $headerTemplate = MailTemplate::findByField('type', 'header_member');
            $subjectHeaderTemplate = $headerTemplate->getTranslatedAttributeValue('subject', $language);
            $contentHeaderTemplate = $headerTemplate->getTranslatedAttributeValue('content', $language);

            $footerTemplate = MailTemplate::findByField('type', 'footer_member');
            $subjectFooterTemplate = $footerTemplate->getTranslatedAttributeValue('subject', $language);
            $contentFooterTemplate = $footerTemplate->getTranslatedAttributeValue('content', $language);

            $recipientEmail = $to->email;
            $recipientName = $to->getName();

            $params['recipient_email'] = $recipientEmail;
            $params['recipient_name'] = $recipientName;
        } else {
            $headerTemplate = MailTemplate::findByField('type', 'header');
            $subjectHeaderTemplate = $headerTemplate->getTranslatedAttributeValue('subject', $language);
            $contentHeaderTemplate = $headerTemplate->getTranslatedAttributeValue('content', $language);

            $footerTemplate = MailTemplate::findByField('type', 'footer');
            $subjectFooterTemplate = $footerTemplate->getTranslatedAttributeValue('subject', $language);
            $contentFooterTemplate = $footerTemplate->getTranslatedAttributeValue('content', $language);

            $recipientEmail = $to;

            $params['recipient_email'] = $recipientEmail;
            if (empty($params['recipient_name'])) {
                $params['recipient_name'] = __('Sir/Madam');
            }

            $recipientName = $params['recipient_name'];
        }

        if (! in_array($type, $typeIgnoreList)) {
            if (! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            if (MailUnsubscribe::getByEmail($recipientEmail)) {
                return;
            }
        }

        if (! empty($params['subject'])) {
            $subjectTemplate = $params['subject'];
        }

        if (! empty($params['content'])) {
            $contentTemplate = $params['content'];
        }
        
        foreach ($params as $key => $value) {
			if (is_array($value) || is_object($value)) {
				continue;
			}
            if ($key != 'blade') {
                $key = '['.$key.']';

                $value = str_replace('&amp;nbsp;', ' ', $value);
                $value = str_replace('&nbsp;', ' ', $value);
                $value = nl2br(e($value), false);

                // Replace
                $subjectTemplate = Str::replace($key, $value, $subjectTemplate);
                $contentTemplate = Str::replace($key, $value, $contentTemplate);

                $subjectHeaderTemplate = Str::replace($key, $value, $subjectHeaderTemplate);
                $contentHeaderTemplate = Str::replace($key, $value, $contentHeaderTemplate);

                $subjectFooterTemplate = Str::replace($key, $value, $subjectFooterTemplate);
                $contentFooterTemplate = Str::replace($key, $value, $contentFooterTemplate);
            }
        }

        $contentTemplate = Str::replace('[header]', $contentHeaderTemplate, $contentTemplate);
        $contentTemplate = Str::replace('[footer]', $contentFooterTemplate, $contentTemplate);

        $defaultLanguage = App::getLocale();
        App::setLocale($language);

        if (! empty($params['blade'])) {
            $contentBlade = view($params['blade'], $params)->render();

            $contentTemplate = Str::replace('[blade]', $contentBlade, $contentTemplate);
        }

        try {
            $unsubscribeLink = route('web.unsubscribe.email',[
                'email' => $recipientEmail,
                'hash' => getHashUnsubscribeFromEmail($recipientEmail)
            ]);

            //check before send
            $check = true;
            
            if (setting('mail.engine', 'smtp') == 'smtp') {
                if (! setting('mail.host')) {
                    $check = false;
                }
            }

            if ($check) {
                MailManager::send('shaun_core::mail.template_default', ['contentTemplate' => $contentTemplate, 'unsubscribeLink' => $unsubscribeLink], function ($message) use ($recipientEmail, $recipientName, $subjectTemplate, $params, $type) {
                    if (! empty($params['cc'])) {
                        if (is_string($params['cc'])) {
                            $params['cc'] = [($params['cc'])];
                        }

                        $message->cc($params['cc']);
                    }

                    if (! empty($params['bcc'])) {
                        if (is_string($params['bcc'])) {
                            $params['bcc'] = [($params['bcc'])];
                        }

                        $message->bcc($params['bcc']);
                    }

                    if (! empty($params['files'])) {
                        if (is_string($params['files'])) {
                            $params['files'] = [($params['files'])];
                        }

                        foreach ($params['files'] as $file) {
                            $message->attach($file);
                        }
                    }

                    $message->to($recipientEmail, $recipientName);
                    $message->subject(htmlspecialchars_decode(html_entity_decode($subjectTemplate), ENT_QUOTES));
                });

                if (setting('mail.log')) {
                    Log::channel('shaun_mail')->info('send_email', [
                        'email' => $recipientEmail,
                        'type' => $type,
                        'prarms' => $params,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::channel('shaun_mail')->error($e->getMessage());
        }

        App::setLocale($defaultLanguage);
    }
}
