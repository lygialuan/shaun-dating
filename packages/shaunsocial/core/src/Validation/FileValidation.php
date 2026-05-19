<?php


namespace Packages\ShaunSocial\Core\Validation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class FileValidation implements ValidationRule
{
    private $mimes;

    private $max;

    public function __construct($mimes = '', $max = null)
    {
        if ($max === null) {
            $max = getMaxUploadFileSize();
        }
        $this->mimes = removeSpaceString($mimes);
        $this->max = $max;
    }

    public function validate($attribute, $value, $fail): void
    {
        $rules = ['max:'.$this->max];
        if ($this->mimes) {
            $rules[] = 'file_extension:'.$this->mimes;
        }
        
        $nameFile = $value->getClientOriginalName();

        $validation = Validator::make(
            [$attribute => $value],
            [
                $attribute => $rules,
            ],
            [
                $attribute.'.file_extension' => __('The :file has an invalid extension. Valid extension(s): :extensions', ['file' => $nameFile, 'extensions' => $this->mimes]).'.',
                $attribute.'.max' => __('The :file is too large, maximum file size is :limit', ['file' => $nameFile, 'limit' => $this->max.'Kb']).'.',
            ]
        );

        if ($validation->fails()) {
            $fail($validation->getMessageBag()->first());
        }
    }
}
