<?php

/**
 * Microsoft Translator API.
 *
 * @author Vishwa Datta
 *
 * @version 1.0
 */
class MicrosoftTranslator
{
    private $expires_at = 0;
    private $access_token = null;

    public function __construct($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    /**
     * Performs Requests to Microsoft API with all required authencation.
     *
     * @param string $end_point API Endpoint
     *
     * @return xml XML Response from microsoft
     */
    private function makeRequest($end_point)
    {
        $url = 'http://api.microsofttranslator.com/v2/Http.svc'.$end_point;
        $headers =  [
            'Authorization: Bearer '.$this->getAccessToken(),
            'Content-Type: text/xml',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);

        // get error code & throw an exception if error is present
        $curl_errno = curl_errno($ch);
        if ($curl_errno) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);

        return $resp;
    }

    /**
     * Returns or generates a new access token.
     *
     * @return string access token
     */
    private function getAccessToken()
    {
        // Checking if access token already exists & not expired
        if ($this->access_token && ($this->expires_at - strtotime('now')) > 0) {
            return $this->access_token;
        }

        // Generating new one
        $auth_url = 'https://datamarket.accesscontrol.windows.net/v2/OAuth2-13';
        $post_params = 'grant_type=client_credentials&client_id='.urlencode($this->client_id).
            '&client_secret='.urlencode($this->client_secret).'&scope=http://api.microsofttranslator.com';

        // making request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);

        // get error code & throw an exception if error is present
        $curl_errno = curl_errno($ch);
        if ($curl_errno) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($result);

        // check if there is a microsoft error, if yes, through exception
        if (property_exists($result, 'error')) {
            throw new Exception($result->error_description);
        }

        $this->expires_at = strtotime("+{$result->expires_in} seconds");

        return $result->access_token;
    }

    /**
     * Translate text from one language to other.
     *
     * @param string $text Text to be translated
     * @param string $to   to language code
     * @param string $from from language code (optional)
     *
     * @return string Translated text. Raises an exception if failed
     */
    public function translate($text, $to, $from = null)
    {
        $params = 'text='.urlencode($text).'&to='.$to.'&from='.$from;
        $end_point = "/Translate?$params";

        $result = $this->makeRequest($end_point);

        $data = simplexml_load_string($result);

       // check if there is an error & throw
       if (property_exists($data, 'body')) {
           $err_info = (array) $data->body->p;
           throw new Exception($err_info[2]);
       }

        $arr = (array) $data[0];

        return $arr[0];
    }

    /**
     * Get List of All languages Codes supported by Microsoft Translator.
     *
     * @return array array of language codes
     */
    public function getSupportedLangs()
    {
        $end_point = '/GetLanguagesForTranslate';
        $result = $this->makeRequest($end_point);
        $data = simplexml_load_string($result);

        return $data->string;
    }

    /**
     * Detect Language of the text provided.
     *
     * @param string $text text for detection
     *
     * @return string language code of the text
     */
    public function detect($text)
    {
        $end_point = '/Detect?text='.urlencode($text);
        $result = $this->makeRequest($end_point);
        $data = (array) simplexml_load_string($result);

        return $data[0];
    }
}
