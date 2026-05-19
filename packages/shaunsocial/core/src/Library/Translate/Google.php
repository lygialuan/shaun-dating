<?php 

namespace Packages\ShaunSocial\Core\Library\Translate;

use Exception;
use GuzzleHttp\Client;

class Google extends Base
{
    public function translate($text, $language)
    {
        $result['status'] = false;

        if (empty($this->config['api_key'])) {
            $result['message'] = __('API key not valid. Please pass a valid API key');
        } else {
            $client = new Client();
            $options = getClientOptions();
            $options['form_params'] = [
                'key' => $this->config['api_key'],
                'q' => $text,
                'target' => $language,
                'format' => 'text'
            ];
            try {
                $response = $client->post('https://www.googleapis.com/language/translate/v2', $options);
                $body = json_decode((string)$response->getBody(), true);
                if (! empty($body['data']['translations'][0]['translatedText'])) {
                    $result['status'] = true;
                    $result['content'] = $body['data']['translations'][0]['translatedText'];
                } else {
                    $result['message'] = __('Something went wrong. Please try again later.');
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'API key not valid. Please pass a valid API key') !== FALSE) {
                    $result['message'] = __('API key not valid. Please pass a valid API key');
                } else {
                    $result['message'] = __('Something went wrong. Please try again later.');
                }
            }
        }

        return $result;
    }
}