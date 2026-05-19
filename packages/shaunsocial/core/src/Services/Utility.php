<?php


namespace Packages\ShaunSocial\Core\Services;

use GuzzleHttp\Client;
use Packages\ShaunSocial\Core\Models\Link;
use Symfony\Component\HttpFoundation\File\File as FileCore;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\Video;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Traits\Utility as TraitsUtility;

class Utility
{
    use TraitsUtility;

    protected $imageTypes = [
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp'
    ];

    public function getContent($url)
    {
        $options = getClientOptions();
        
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,  $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $options['headers']['User-Agent']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $options['timeout']);

        $data = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        
        if(isset($info['http_code']) && $info['http_code'] == 302 && !empty($info['redirect_url'])){
            return $this->getContent($info['redirect_url']);
        }else{
            return trim($data);
        }
    }

    function parseLink($url, $viewerId)
    {
        // check link youtube
        $youtubeId = getYoutubeEmbedUrl($url);
        if ($youtubeId)
        {
            $video = Cache::remember('parse_link_youtube_'.$url, config('shaun_core.cache.time.link'), function () use ($youtubeId) {
                $response = Http::timeout(config('shaun_core.core.http_timeout'))->get('https://www.youtube.com/oembed?format=json&url='.urlencode('https://www.youtube.com/watch?v='.$youtubeId));
                return $response->json();
            });
            if ($video) {
                return $this->createLink([
                    'title' => $video['title'],
                    'url' => $url,
                    'description' => '',
                    'image' => isset($video['thumbnail_url']) ? $video['thumbnail_url'] : '',
                    'user_id' => $viewerId,
                    'youtube_id' => $youtubeId
                ]);
            }
        }

        // check link vimeo
        if ( strpos( $url, 'vimeo.com' ) !== false ){
			preg_match('/(\d+)/', $url, $matches);

            if (!empty($matches[0])) {
                $id = $matches[0];

                $body = Cache::remember('parse_link_vimeo_'.$url, config('shaun_core.cache.time.link'), function () use ($id){
                    $response = Http::timeout(config('shaun_core.core.http_timeout'))->get('http://vimeo.com/api/v2/video/' . $id . '.php');
                    return $response->body();
                });

                if (!strstr($body, 'not found')){
                    $video = unserialize($body);
                    if ($video) {
                        return $this->createLink([
                            'title' => $video[0]['title'],
                            'url' => $url,
                            'description' => strip_tags(str_replace('<br />', '', $video[0]['description'])),
                            'image' => $video[0]['thumbnail_large'],
                            'user_id' => $viewerId
                        ]);
                    }
                }
            }
        }

        //check link tiktok
        if(strpos( $url, 'https://vt.tiktok.com' ) !== false){
            $url = $this->getFinalDestinationURL($url);
        }
        if ( strpos( $url, 'https://www.tiktok.com' ) !== false ){
            $body = Cache::remember('parse_link_tiktok_'.$url, config('shaun_core.cache.time.link'), function () use ($url){
                $response = Http::timeout(config('shaun_core.core.http_timeout'))->get('https://www.tiktok.com/oembed?url='. $url);
                return $response->body();
            });

            $video = json_decode($body, true);
            if (is_array($video) && !empty($video['title'])) {
                return $this->createLink([
                    'title' => $video['title'],
                    'url' => $url,
                    'description' => '',
                    'image' => ! empty($video['thumbnail_url']) ? $video['thumbnail_url'] : '',
                    'user_id' => $viewerId,
                    'tiktok_id' => ! empty($video['embed_product_id']) ? $video['embed_product_id'] : '',
                ]);
            }
        }

        //check ignore domain
        foreach (config('shaun_core.core.check_fetch_domain') as $domain) {
            if (strpos($url, $domain) === 0) {
                return null;
            }
        }

        try {
            $data = Cache::get('parse_link_'.$url);
            if (! $data) {
                $client = new Client();
                $options = getClientOptions();
                $options['curl'][CURLOPT_NOBODY] = true;

                $responseHeader = $client->get($url, $options);
                $statusCodeHeader = $responseHeader->getStatusCode();
                if ($statusCodeHeader == 200) {
                    $data = null;
                    //header image
                    $header = $responseHeader->getHeader('Content-Type');
                    if (isset($header[0])) {
                        $header = $header[0];
                    }

                    if (in_array($header,$this->imageTypes)) {
                        $data = [
                            'title' => '',
                            'url' => $url,
                            'description' => '',
                            'image' => $url
                        ];
                    } elseif (strpos($header,'application') !== false) {
                        $data = [
                            'title' => $url,
                            'url' => $url,
                            'description' => '',
                            'image' => ''
                        ];
                    } else {
                        $content = $this->getContent($url);
                        if ($content) {
                            $metaTags =$this->getMetaTags($content);
                            $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $content, $matches) ? $matches[1] : null;

                            $description = '';
                            if (! empty($metaTags['description'])) {
                                $description = $metaTags['description'];
                            } elseif (! empty($metaTags['og:description'])) {
                                $description = $metaTags['og:description'];
                            } elseif (! empty($metaTags['twitter:description'])) {
                                $description = $metaTags['twitter:description'];
                            } 

                            $image = '';
                            if (! empty($metaTags['image'])) {
                                $image = $metaTags['image'];
                            } elseif (! empty($metaTags['og:image'])) {
                                $image = $metaTags['og:image'];
                            } elseif (! empty($metaTags['twitter:image'])) {
                                $image = $metaTags['twitter:image'];
                            }
        
                            $data = [
                                'title' => $title ? html_entity_decode($title) : $url,
                                'url' => $url,
                                'description' => html_entity_decode($description),
                                'image' => $image
                            ];
                        }
                    }
                    if ($data) {
                        Cache::add('parse_link_'.$url, $data, config('shaun_core.cache.time.link'));
                    }
                }
            
            }
            if ($data) {
                $data['user_id'] = $viewerId;
                return $this->createLink($data);
            }
        } catch (Exception $e) {
            
        }

        return null;
    }

    function createLink($data)
    {
        $link = Link::create($data);

        if (!empty($data['image'])) {
            $photoPath = File::downloadPhoto($data['image']);
            if ($photoPath) {
                $storageFile = File::storePhoto($photoPath, [
                    'parent_type' => 'link',
                    'parent_id' => $link->id,
                    'user_id' => $link->user_id,
                ]);

                $link->update([
                    'photo_file_id' => $storageFile->id,
                ]);

                $link->setFile('photo_file_id', $storageFile);
            }
        }

        return $link;
    }

    function getMetaTags($str)
    {
        $pattern = '
            ~<\s*meta\s

            # using lookahead to capture type to $1
                (?=[^>]*?
                \b(?:name|property|http-equiv)\s*=\s*
                (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
                ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
            )

            # capture content to $2
            [^>]*?\bcontent\s*=\s*
                (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
                ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
            [^>]*>

            ~ix';

        if (preg_match_all($pattern, $str, $out)) {
            return array_combine($out[1], $out[2]);
        }
        
        return array();
    }

    function storeVideo($file, $viewerId, $isConverted = false, $convertNow = false, $permissionLimitType = '', $addWatermark = false)
    {
        $ffmpeg = getFFMpeg();
        $result = ['status' => false, 'message' => __('There was an error while uploading your video.')];
        if ($ffmpeg) {
            //validate video
            $duration = $ffmpeg->getFFProbe()->format($file->getRealPath())->get('duration');
            if (! $duration) {
                $result['message'] = __('Video is not validate');
                return $result;
            }
            $duration = floor($duration);

            //check duration
            if (setting('feature.video_max_duration') && $duration > setting('feature.video_max_duration')) {
                $result['message'] = __('The video must not be longer than :second seconds.', ['second' => setting('feature.video_max_duration')]);
                return $result;
            }

            //check permission
            $user = User::findByField('id', $viewerId);
            $permissionLimitType = $permissionLimitType ? $permissionLimitType : 'post.video_max_duration';
            $this->checkPermissionHaveValue($permissionLimitType, $duration, $user);

            $converted = false;
            if ($duration <= config('shaun_core.video.limit_duration_convert_now')) {
                $converted = true;
            }

            if ($convertNow) {
                if ($duration > config('shaun_core.video.limit_duration_convert_now')) {
                    $result['message'] = __('The video must not be longer than :second seconds.', ['second' => config('shaun_core.video.limit_duration_convert_now')]);
                    return $result;
                }
            }

            if (env('VIDEO_NO_NEED_CONVERT_MP4')) {
                $videoExtension = $file->getExtension();
                if ($file instanceof UploadedFile) {
                    $videoExtension = $file->getClientOriginalExtension();
                }
                if ($videoExtension == 'mp4') {
                    $isConverted = true;
                }
            }

            $video = Video::create([
                'is_converted' => $converted || $isConverted,
                'user_id' => $viewerId,
                'duration' => $duration
            ]);
            //get thumb
            $thumb = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.jpg';
            $videoConvert = $ffmpeg->open($file->getRealPath());
            $fileVideo = $file->getRealPath();
            $videoExtension = $file->getExtension();
            $videoName = $file->getFilename();
            if ($file instanceof UploadedFile) {
                $videoExtension = $file->getClientOriginalExtension();
                $videoName = $file->getClientOriginalName();
            }
            
            if ($converted && !$isConverted) {
                $fileVideo = $this->convertVideo($videoConvert);
                $info = pathinfo($fileVideo);
                $videoExtension = $info['extension'];
                unlink($file->getRealPath());
                $videoConvert = $ffmpeg->open($fileVideo);
                $videoConvert->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                    ->save($thumb);
            } else {
                $videoConvert->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                    ->save($thumb);
            }

            $storageThumbFile = File::storePhoto($thumb, [
                'parent_type' => 'video_thumb',
                'parent_id' => $video->id,
                'user_id' => $viewerId,
            ], $addWatermark);

            $video->setFile('thumb_file_id', $storageThumbFile);

            //store file
            
            $storageVideoFile = File::store(new FileCore($fileVideo), [
                'parent_type' => 'video',
                'parent_id' => $video->id,
                'user_id' => $viewerId,
                'extension' => $videoExtension,
                'name' => $videoName
            ]);

            $video->setFile('file_id', $storageVideoFile);

            $video->update([
                'thumb_file_id' => $storageThumbFile->id,
                'file_id' => $storageVideoFile->id
            ]);
            $result['status'] = true;
            $result['video'] = $video;
        }

        return $result;
    }

    public function convertVideo($videoConvert)
    {
        $dimensions = $videoConvert->getFFProbe()->streams($videoConvert->getPathFile())->videos()->first()->getDimensions();
        $resolutions = $this->getVideoResolutions($dimensions->getWidth(), $dimensions->getHeight());
        $format = new \FFMpeg\Format\Video\X264('aac');
        $format->setInitialParameters(array('-noautorotate'));
        $format->setAdditionalParameters(array('-preset', 'veryfast', '-strict', 'experimental','-pix_fmt', 'yuv420p','-profile:v', 'baseline', '-level', 3.0));

        $value = $resolutions['720p'];
        $prop = round(($value['heightR']*16)/$value['widthR']) == 9;
        $width = $value['width'];
        $height = $value['height'];

        //If the dimensions of the video are not proportional, check which measurement is larger and use it as a base
        if(!$prop){
            if($value['widthR'] == $value['heightR']){
                $width = $value['height'];
                $height = $value['height'];
            }else if($value['widthR'] > $value['heightR']){
                $height = round(($value['widthR']/16)*9);
            }else{
                $width = round(($value['heightR']/9)*16);
            }
        }
        $format->setKiloBitrate(0);
        $width = ($width % 2 == 0) ? $width : $width+1;
        $height = ($height % 2 == 0) ? $height : $height+1;
        
        $file = storage_path('tmp') . DIRECTORY_SEPARATOR . Str::uuid() . '.mp4';
        $videoConvert->filters()
          ->resize(new \FFMpeg\Coordinate\Dimension($width, $height),\FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET,true)
          ->synchronize();
        $videoConvert->save($format, $file);

        return $file;
    }

    public function getVideoResolutions($width, $height)
    {
        $dimensions = [
            'width' => $width,
            'height' => $height
        ];
        return  [
            '2160p' => [
                'suport' => ($dimensions['width'] >= 3840 || $dimensions['height'] >= 2160) ? true : false,
                'widthS' => $dimensions['width'] >= 3840,
                'heightS' => $dimensions['height'] >= 2160,
                'widthR' => $dimensions['width'],
                'heightR' => $dimensions['height'],
                'width' => 3840,
                'height' => 2160,
                'kilobitrate' => 6144,
                'bandwidth' => 20971520
            ],
            '1440p' => [
                'suport' => ($dimensions['width'] >= 2560 || $dimensions['height'] >= 1440) ? true : false,
                'widthS' => $dimensions['width'] >= 2560,
                'heightS' => $dimensions['height'] >= 1440,
                'widthR' => $dimensions['width'],
                'heightR' => $dimensions['height'],
                'width' => 2560,
                'height' => 1440,
                'kilobitrate' => 4096,
                'bandwidth' => 15728640
            ],
            '1080p' => [
                'suport' => ($dimensions['width'] >= 1920 || $dimensions['height'] >= 1080) ? true : false,
                'widthS' => $dimensions['width'] >= 1920,
                'heightS' => $dimensions['height'] >= 1080,
                'widthR' => $dimensions['width'],
                'heightR' => $dimensions['height'],
                'width' => 1920,
                'height' => 1080,
                'kilobitrate' => 4096,
                'bandwidth' => 10485760
            ],
            '720p' => [
                'suport' => ($dimensions['width'] >= 1280 || $dimensions['height'] >= 720) ? true : false,
                'widthS' => $dimensions['width'] >= 1280,
                'heightS' => $dimensions['height'] >= 720,
                'widthR' => $dimensions['width'],
                'heightR' => $dimensions['height'],
                'width' => 1280,
                'height' => 720,
                'kilobitrate' => 2048,
                'bandwidth' => 7340032
            ],
            '480p' => [
                'suport' => ($dimensions['width'] >= 854 || $dimensions['height'] >= 480) ? true : false,
                'widthS' => $dimensions['width'] >= 854,
                'heightS' => $dimensions['height'] >= 480,
                'widthR' => $dimensions['width'],
                'heightR' => $dimensions['height'],
                'width' => 854,
                'height' => 480,
                'kilobitrate' => 750,
                'bandwidth' => 4718592
            ],
            '360p' => [
                'suport' => ($dimensions['width'] >= 640 || $dimensions['height'] >= 360) ? true : false,
                'widthS' => $dimensions['width'] >= 640,
                'heightS' => $dimensions['height'] >= 360,
                'widthR' => $dimensions['width'],
                'heightR' => $dimensions['height'],
                'width' => 640,
                'height' => 360,
                'kilobitrate' => 276,
                'bandwidth' => 3145728
            ],
            '240p' => [
                'suport' => ($dimensions['width'] >= 426 || $dimensions['height'] >= 240),
                'widthS' => $dimensions['width'] >= 426,
                'heightS' => $dimensions['height'] >= 240,
                'widthR' => $dimensions['width'],
                'heightR' => $dimensions['height'],
                'width' => 426,
                'height' => 240,
                'kilobitrate' => 150,
                'bandwidth' => 1572864
            ],
            '144p' => [
                'suport' => true,
                'widthS' => $dimensions['width'] >= 256,
                'heightS' => $dimensions['height'] >= 144,
                'widthR' => $dimensions['width'],
                'heightR' => $dimensions['height'],
                'width' => 256,
                'height' => 144,
                'kilobitrate' => 80,
                'bandwidth' => 524288
            ]
        ];
    }

    public function convertVideoFromVideoModel($video)
    {
        $ffmpeg = getFFMpeg();
        if ($ffmpeg) {
            $file = $video->getFile('file_id');
            $path = storage_path('app/public/'.$file->storage_path);
            if (! file_exists($path)) {
                $url = $file->getUrl();
                $path = File::downloadFile($url);
                if (! $path) {
                    return false;
                }
            }
            try {
                $videoConvert = $ffmpeg->open($path);
                $fileVideo = $this->convertVideo($videoConvert);
                $videoConvert = $ffmpeg->open($fileVideo);
                $thumb = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.jpg';
                $videoConvert->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                    ->save($thumb);
    
                $storageThumbFile = File::storePhoto($thumb, [
                    'parent_type' => 'video_thumb',
                    'parent_id' => $video->id,
                    'user_id' => $video->user_id,
                ]);
    
                //store file
                $storageVideoFile = File::store(new FileCore($fileVideo), [
                    'parent_type' => 'video',
                    'parent_id' => $video->id,
                    'user_id' => $video->user_id,
                ]);
    
                $video->update([
                    'is_converted' => true,
                    'file_id' => $storageVideoFile->id,
                    'thumb_file_id' =>  $storageThumbFile->id
                ]);

                return true;
            } catch (Exception $e) {

            }
        }

        return false;
    }

    public function addSongToVideoFromVideoModel($video, $songFile)
    {
        $file = $video->getFile('file_id');
        $path = storage_path('app/public/'.$file->storage_path);
        if (! file_exists($path)) {
            $url = $file->getUrl();
            $path = File::downloadFile($url);
            if (! $path) {
                return false;
            }
        }

        $songPath = storage_path('app/public/'.$songFile->storage_path);
        if (! file_exists($songPath)) {
            $url = $songFile->getUrl();
            $songPath = File::downloadFile($url);
            if (! $songPath) {
                return false;
            }
        }

        try {
            $fileVideo = storage_path('tmp') . DIRECTORY_SEPARATOR . Str::uuid() . '.mp4';
            $ffmpeg = getFFMpeg();
            $cmd = $ffmpeg->getFFMpegDriver()->getProcessBuilderFactory()->getBinary(). ' -i '.$path.' -stream_loop -1 -i '.$songPath.' -c:v copy -shortest -map 0:v -map 1:a -y '.$fileVideo;
            Process::run($cmd);

            if (! file_exists($fileVideo)) {
                return false;
            }

            //store file
            $storageVideoFile = File::store(new FileCore($fileVideo), [
                'parent_type' => 'video',
                'parent_id' => $video->id,
                'user_id' => $video->user_id,
            ]);

            $video->update([
                'file_id' => $storageVideoFile->id,
            ]);

            return true;
        } catch (Exception $e) {

        }

        return false;
    }
    
    public function getAudioDuration($filePath, $permission, $viewer)
    {
        $ffmpeg = getFFMpeg();
        $duration = $ffmpeg->getFFProbe()->format($filePath)->get('duration');
        if (! $duration) {
            throw new MessageHttpException(__('Audio is not validate'));
        }
        $duration--;
        //check duration
        if (setting('feature.audio_max_duration') && $duration > setting('feature.audio_max_duration')) {
            throw new MessageHttpException(__('The audio must not be longer than :second seconds.', ['second' => setting('feature.audio_max_duration')]));
        }

        $this->checkPermissionHaveValue($permission, $duration, $viewer);

        return $duration;
    }

    public function getFinalDestinationURL($url) 
    {
        $headers = get_headers($url, 1);
    
        // If "Location" header is set, it contains the final destination URL
        if (isset($headers['Location'])) {
            // If "Location" header is an array, get the last element
            if (is_array($headers['Location'])) {
                return end($headers['Location']);
            } else {
                return $headers['Location'];
            }
        }
    
        // If "Location" header is not set, return the original URL
        return $url;
    }

    public function updateLanguagesExist()
    {
        $languages = Language::getAll();
        $serverInstallPhrases = getServerLanguageArray('install');
        $clientInstallPhrases = getClientLanguageArray('install');

        foreach ($languages as $language) {
            $serverPhrases = getServerLanguageArray($language->key);
            $clientPhrases = getClientLanguageArray($language->key);

            $arrayDiff = array_diff($serverInstallPhrases, $serverPhrases);
            $serverPhrases = $serverPhrases + $arrayDiff;

            $arrayDiff = array_diff($clientInstallPhrases, $clientPhrases);
            $clientPhrases = $clientPhrases + $arrayDiff;

            writeFileLanguageJson(getServerLanguagePath($language->key), $serverPhrases);
            writeFileLanguageJson(getClientLanguagePath($language->key), $clientPhrases);
        }
    }
}
