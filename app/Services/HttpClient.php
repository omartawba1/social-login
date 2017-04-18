<?php

namespace App\Services;

class HttpClient
{
    /**
     * HttpClient constructor to set the API URL & Headers.
     * 
     * @return void
     */
    public function __construct()
    {
        // set some vars here
    }
    
    /**
     * Executing the API Call
     * 
     * @param $url
     * @param $headers
     * @param $method
     * @param $params
     *
     * @return array
     */
    public function run($url, $headers = [], $method = 'GET', $params = [])
    {
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}
