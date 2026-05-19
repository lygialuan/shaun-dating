<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Invite;

use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\CsvValidation;

class StoreCsvInviteValidate extends BaseFormRequest
{
    use Utility;
    
    public $emails = [];
    public function rules()
    {
        return [
            'csv_file' => [
                'required',
                new CsvValidation()
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();

                if (setting('spam.invite_email_enable_recapcha')) {
                    $result = $this->validateSpam($this->request->all());
                    if (! $result['status']) {
                        throw new MessageHttpException($result['message']); 
                    }
                }
                
                $file = $this->file('csv_file');

                $fileHandle = fopen($file, 'r');
                $emails = [];
                while (!feof($fileHandle)) {
                    $email = fgetcsv($fileHandle, 0, ',');
                    if (isset($email[0])) {
                        $validatorMail = Validator::make(['email' => trim($email[0])],[
                            'email' => 'email|max:255',
                        ]);

                        if ($validatorMail->fails()) {
                            continue;
                        }

                        $emails[] = trim($email[0]);
                    }
                }
                fclose($fileHandle);

                if (!count($emails)) {
                    return $validator->errors()->add('csv_file', __('The file you uploaded is not valid.'));
                }

                if (! $this->checkInviteLimit($user, count($emails))) {
                    return $validator->errors()->add('csv_file', __('Number of invitations that can be sent per day is :max', ['max' => getInviteLimit()]).'.');
                }
                
                $this->emails = $emails;
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'csv_file.required' => __('The csv file is required.'),
            'csv_file.uploaded' => __('The csv file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
        ];
    }
}
