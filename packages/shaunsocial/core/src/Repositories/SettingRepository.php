<?php


namespace Packages\ShaunSocial\Core\Repositories;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Models\Setting;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Validation\FileValidation;
use Packages\ShaunSocial\Core\Validation\PhotoValidation;

class SettingRepository
{
    public function saveSetting($request)
    {
        $settings = Setting::where(['group_id' => $request->group_id, 'hidden' => 0])->get();
        $this->saveSettings($settings, $request);
    }

    public function saveSingle($request)
    {
        $settings = Setting::where('key', $request->key)->get();
        $this->saveSettings($settings, $request);

        return $settings->first();
    }

    public function saveSettings($settings, $request)
    {
        //validate file
        foreach ($settings as $setting) {
            switch ($setting->type) {
                case 'image':
                    $field = str_replace('.', '_', $setting->key);
                    $photoValidate = new PhotoValidation;
                    $params = $setting->getParams();
                    if (!empty($params['extensions'])) {
                        $photoValidate = new FileValidation($params['extensions']);
                    }

                    $validation = Validator::make($request->all(), [
                        $field => $photoValidate,
                    ],[
                        $field.'.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
                    ]);

                    if ($validation->fails()) {
                        throw new Exception($validation->getMessageBag()->first());
                    }

                    break;
            }
        }

        foreach ($settings as $setting) {
            $content = $this->getContentBasedOnType($request, $setting);
            $setting->value = $content;
            $setting->save();

            $groupSub = $setting->getGroupSub();
            $packageName = getPackageName(str_replace('shaun_', '', $groupSub->package));
            $packageName = 'Packages\ShaunSocial\\'.$packageName.'\Repositories\Helpers\Package';
            if (class_exists($packageName)) {
                $package = app($packageName);
                if (method_exists($package, 'afterSaveSetting')) {
                    $package->afterSaveSetting($setting);
                }
            }
        }

        //clear setting cache
        clearSettingCache();
    }

    public function getContentBasedOnType(Request $request, $setting)
    {
        $content = null;
        $field = str_replace('.', '_', $setting->key);

        if ($request->has($field)) {
            switch ($setting->type) {
                case 'image':
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);

                        $storageFile = File::storePhoto($file, array_merge($setting->getParams(), [
                            'parent_id' => $setting->id,
                            'parent_type' => 'setting',
                        ]));
                        $content = $storageFile->id;
                    } else {
                        $content = $setting->value;
                    }
                    break;
                default:
                    $content = $request->input($field);
            }
        } else {
            $content = $setting->value;
        }

        return $content;
    }
}
