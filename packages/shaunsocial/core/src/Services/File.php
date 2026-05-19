<?php


namespace Packages\ShaunSocial\Core\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Symfony\Component\HttpFoundation\File\File as FileCore;
use GuzzleHttp\Client;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Library\GetMostCommonColors;
use Packages\ShaunSocial\Core\Models\User;

class File
{
    public function storePhoto($file, $params = [], $addWatermark = false)
    {
        if (is_string($file)) {
            $file = new FileCore($file);
        }

        //disable resize file
        $extension = $file->getExtension();
		if ($file instanceof UploadedFile) {
			$params['extension'] = $extension = $file->getClientOriginalExtension();
			$params['name'] = $file->getClientOriginalName();
		}        
        $extension = strtolower($extension);
        $fileTmp = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.'.$extension;
        $disableResize = in_array($extension, ['gif', 'ico', 'svg']);
        if ($extension == 'heic') {
            try {
                if (! class_exists('Imagick')) {
                    throw new Exception(__("The file uploaded is in a format that we don't support."));
                }
                // Create a new Imagick object from the HEIC file
                $image = new \Imagick($file->getRealPath());

                // Set the output format to JPEG
                $image->setImageFormat('jpeg');

                // Optionally set compression quality for JPEG
                $image->setCompression(\Imagick::COMPRESSION_JPEG);
                $image->setCompressionQuality(90); // Adjust quality as needed

                // Save the converted image
                $fileTmp = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.jpg';
                $image->writeImage($fileTmp);
                unlink($file->getRealPath());
                return $this->storePhoto($fileTmp);

            } catch (Exception $e) {
                $fileName = $file->getFilename();
                if (!empty($params['name'])) {
                    $fileName = $params['name'];
                }
                throw new MessageHttpException(__("The :file is in a format that we don't support.", ['file' => $fileName]));
            }
        }
        $manager = new ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );
        if (! $disableResize) {
            try {
                $image = $manager->read($file->getRealPath());
            } catch (\Exception $e) {
                $fileName = $file->getFilename();
                if (!empty($params['name'])) {
                    $fileName = $params['name'];
                }
                throw new MessageHttpException(__("The :file is in a format that we don't support.", ['file' => $fileName])); 
            }
    
            $image->orient();
            $maxWidth = config('shaun_core.file.photo.max_width');
            $maxHeight = config('shaun_core.file.photo.max_height');

            if ($image->width() > $maxWidth) {
                $image->scale($maxWidth, null);
            }

            if ($image->height() > $maxHeight) {
                $image->scale(null, $maxHeight);
            }

            if (! empty($params['resize_size'])) {
                $this->resizeImage($image, $params['resize_size']);
            }

            // Add watermark text for image
            if($addWatermark && setting('watermark.enable') && !empty($params['user_id'])){
                $user = User::findByField('id', $params['user_id']);
                if ($user) {
                    $watermarkText = setting('watermark.text') ? setting('watermark.text') : route('web.user.profile',['user_name' => $user->user_name]);
                    $watermarkPosition = setting('watermark.position');
                    $watermarkColor = setting('watermark.text_color') ? setting('watermark.text_color') : '#ffffff';
                    $watermarkSize = setting('watermark.text_size') ? setting('watermark.text_size') : 24;
                    $watermarkStyle = setting('watermark.text_style');
                    $padding = 10;
                    switch ($watermarkPosition) {
                        case 'top_left':
                            $x = $padding;
                            $y = $padding;
                            $align = 'left';
                            $valign = 'top';
                            break;
                        case 'top_center':
                            $x = $image->width() / 2;
                            $y = $padding;
                            $align = 'center';
                            $valign = 'top';
                            break;
                        case 'top_right':
                            $x = $image->width() - $padding;
                            $y = $padding;
                            $align = 'right';
                            $valign = 'top';
                            break;
                        case 'bottom_left':
                            $x = $padding;
                            $y = $image->height() - $padding;
                            $align = 'left';
                            $valign = 'bottom';
                            break;
                        case 'bottom_center':
                            $x = $image->width() / 2;
                            $y = $image->height() - $padding;
                            $align = 'center';
                            $valign = 'bottom';
                            break;
                        case 'bottom_right':
                            $x = $image->width() - $padding;
                            $y = $image->height() - $padding;
                            $align = 'right';
                            $valign = 'bottom';
                            break;
                        case 'middle_left':
                            $x = $padding;
                            $y = $image->height() / 2;
                            $align = 'left';
                            $valign = 'center';
                            break;
                        case 'middle_right':
                            $x = $image->width() - $padding;
                            $y = $image->height() / 2;
                            $align = 'right';
                            $valign = 'center';
                            break;
                        case 'center':
                        default:
                            $x = $image->width() / 2;
                            $y = $image->height() / 2;
                            $align = 'center';
                            $valign = 'center';
                            break;
                    }

                    switch ($watermarkStyle) {
                        case 'bold':
                            $fontPath = public_path('fonts/Inter_Bold.ttf');
                            break;
                        case 'semibold':
                            $fontPath = public_path('fonts/Inter_SemiBold.ttf');
                            break;
                        case 'medium':
                            $fontPath = public_path('fonts/Inter_Medium.ttf');
                            break;
                        case 'regular':
                        default:
                            $fontPath = public_path('fonts/Inter_Regular.ttf');
                            break;
                    }

                    $image->text(
                        $watermarkText,
                        $x,
                        $y,
                        function ($font) use ($watermarkSize, $watermarkColor, $fontPath, $align, $valign) {
                            $font->file($fontPath);
                            $font->size($watermarkSize);
                            $font->color($watermarkColor);
                            $font->align($align);
                            $font->valign($valign);
                            $font->angle(0);
                        }
                    );
                }
            }

            //move file to tmp
            $image->save($fileTmp);
            $mostCollors = new GetMostCommonColors();
            $colors = $mostCollors->Get_Color($fileTmp, 10, true, true, 24);
            $dominantColor = '#'.converBlurColor(array_key_first($colors));
            $storageFile = $this->store(new FileCore($fileTmp), array_merge($params, [
                'params' => json_encode([
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'dominant_color' => $dominantColor
                ]),
            ]), false);

            if (! empty($params['resize_sizes']) && is_array($params['resize_sizes'])) {
                $params['parent_file_id'] = $storageFile->id;
                $childs = [];
                foreach ($params['resize_sizes'] as $type => $size) {
                    $imageResize = $manager->read($fileTmp);
                    $this->resizeImage($imageResize, $size);

                    $fileResizeTmp = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.'.$extension;
                    $imageResize->save($fileResizeTmp);
                    $params['type'] = $type;
                    $childs[] = $this->store(new FileCore($fileResizeTmp), array_merge($params, [
                        'params' => json_encode([
                            'width' => $imageResize->width(),
                            'height' => $imageResize->height(),
                            'dominant_color' => $dominantColor
                        ]),
                    ]));
                }

                $storageFile->update(['has_child' => true]);
                $storageFile->setChilds(collect($childs));
            }

        } else {
            copy($file->getRealPath(), $fileTmp);

            $storageFile = $this->store(new FileCore($fileTmp), array_merge($params, [
                'params' => json_encode([
                    'width' => 0,
                    'height' => 0,
                ]),
            ]), false);
        }

        //unlink
        unlink($fileTmp);
        unlink($file->getRealPath());

        return $storageFile;
    }

    public function store(FileCore $file, $params = [], $isDelete = true)
    {
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $day = $now->format('d');

        $extension = $file->getExtension();
        if (!empty($params['extension'])) {
            $extension = $params['extension'];
        }
        $name = Str::uuid().'.'.$extension;

        $fileName = $file->getFilename();
        if (!empty($params['name'])) {
            $fileName = $params['name'];
        }

        if (empty($params['parent_type'])) {
            $params['parent_type'] = 'default';
        }
        
        $path = 'files/'.$params['parent_type'].'/'.$year.'/'.$month.'/'.$day.'/'.$name;
        if (env('FILESYSTEM_CLOUD')) {
            $path = env('APP_NAME').'/'.$path;
        }

        $result = Storage::put($path, $file->getContent());
        if (! $result) {
            throw new MessageHttpException(__('Error when store file.')); 
        }
        $data = array_merge([
            'service_key' => config('filesystems.default'),
            'extension' => $extension,
            'name' => $fileName,
            'storage_path' => $path,
            'size' => $file->getSize(),
        ], $params);

        if ($isDelete) {
            unlink($file->getRealPath());
        }
        $storageFile = StorageFile::create($data);
        StorageFile::setCacheQueryFieldsResult('id', $storageFile->id, $storageFile);
        
        return $storageFile;
        
    }

    protected function resizeImage($image, $data)
    {
        $width = ! empty($data['width']) ? $data['width'] : null;
        $height = ! empty($data['height']) ? $data['height'] : null;
        $real = ! empty($data['real']) ? $data['real'] : false;

        if (! $real) {
            if ($image->width() > $width) {
                $image->scale($width, null);
            }

            if ($image->height() > $height) {
                $image->scale(null, $height);
            }

            return;
        }
        
        $image->resize($width, $height);
    }

    public function downloadPhoto($url)
    {
        try {
            $options = getClientOptions();
            $client = new Client();
            $response = $client->get($url, $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {                            
                $content = $response->getBody()->getContents();
                $manager = new ImageManager(
                    new \Intervention\Image\Drivers\Gd\Driver()
                );
                $image = $manager->read($content);
                $extension = '';
                switch ($image->encode()->mimetype()) {
                    case 'image/jpeg':
                        $extension = 'jpg';
                        break;
                    case 'image/png':
                        $extension = 'png';
                        break;
                    case 'image/gif':
                        $extension = 'gif';
                        break;
                    case 'image/webp':
                        $extension = 'webp';
                        break;
                }
                if ($extension) {
                    $photoPath = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.'.$extension;
                    if (in_array($extension, ['gif'])) {
                        file_put_contents($photoPath, $content);
                    } else {
                        $image->save($photoPath);
                    }
    
                    return $photoPath;
                }
            }
        } catch (Exception $e) {
        }

        return null;
    }

    public function downloadFile($url)
    {
        try {
            $options = getClientOptions();
            $client = new Client();
            $response = $client->get($url, $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {                            
                $content = $response->getBody()->getContents();
                $info = pathinfo($url);
                $path = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.'.$info['extension'];
                file_put_contents($path, $content);

                return $path;
            }
        } catch (Exception $e) {
        }

        return null;
    }

    public function purrePhoto($file, $params)
    {
        if (class_exists('Imagick')) {
            $checked = false;
            $path = storage_path('app/public/'.$file->storage_path);
            if (! file_exists($path)) {
                $url = $file->getUrl();
                $path = File::downloadFile($url);
                $checked = true;
                if (! $path) {
                    return false;
                }
            }

            try {
                $imagick = new \Imagick($path);
                $imagick->blurImage(20, 10);
                $imagick->setImageFormat('jpeg');
                $fileTmp = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.jpg';
                $imagick->writeImage($fileTmp);

                if ($checked) {
                    unlink($path);
                }

                return $this->store(new FileCore($fileTmp), $params);
            } catch ( Exception $e) {
                if ($checked) {
                    unlink($path);
                }
            }
        }

        return false;
    }
}
