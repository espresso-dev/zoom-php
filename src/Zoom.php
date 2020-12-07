<?php

namespace EspressoDev\Zoom;

class Zoom
{
    const API_URL = 'https://api.zoom.us/v2/';

    const API_OAUTH_URL = 'https://zoom.us/oauth/authorize';

    const API_OAUTH_TOKEN_URL = 'https://zoom.us/oauth/token';
    
    const API_TOKEN_EXCHANGE_URL = 'https://zoom.us/oauth/token';
    
    private $_appId;

    private $_appSecret;

    private $_redirectUri;

    private $_accesstoken;

    private $_timeout = 90000;

    private $_connectTimeout = 20000;

    public function __construct($config = null) 
    {
        if (is_array($config)) {
            $this->setAppId($config['appId']);
            $this->setAppSecret($config['appSecret']);
            $this->setRedirectUri($config['redirectUri']);
            
            if (isset($config['timeout'])) {
                $this->setTimeout($config['timeout']);    
            }
            
            if (isset($config['connectTimeout'])) {
                $this->setConnectTimeout($config['connectTimeout']);    
            }
        } elseif (is_string($config)) {
            // For read-only
            $this->setAccessToken($config);
        } else {
            throw new ZoomException('Error: __construct() - Configuration data is missing.');
        }
    }

    public function getLoginUrl($state = '')
    {
        return self::API_OAUTH_URL . '?client_id=' . $this->getAppId() . '&redirect_uri=' . urlencode($this->getRedirectUri()) . 
            '&response_type=code' . ($state != '' ? '&state=' . $state : '');

        throw new ZoomException("Error: getLoginUrl()");
    }

    public function getUserMeetings($id, $type = 'live', $page_size = 30, $page_number = 1)
    {
        return $this->_makeCall('users/' . $id . '/meetings', compact('type', 'page_size', 'page_number'));
    }

    public function getUserWebinars($id, $page_size = 30, $page_number = 1)
    {
        return $this->_makeCall('users/' . $id . '/webinars', compact('page_size', 'page_number'));
    }

    public function getOAuthToken($code)
    {
        $apiData = array(
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUri(),
            'code' => $code
        );

        $authorization = base64_encode($this->getAppId() . ':' . $this->getAppSecret());
        $header = ['Authorization: Basic ' . $authorization];

        $result = $this->_makeOAuthCall(self::API_OAUTH_TOKEN_URL, $apiData, 'POST', $header);

        return $result;
    }

    public function refreshToken($refreshToken)
    {
        $apiData = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        );

        $authorization = base64_encode($this->getAppId() . ':' . $this->getAppSecret());
        $header = ['Authorization: Basic ' . $authorization];

        $result = $this->_makeOAuthCall(self::API_OAUTH_TOKEN_URL, $apiData, 'POST', $header);

        return $result;
    }

    protected function _makeCall($function, $params = null, $method = 'GET')
    {
        if (!isset($this->_accesstoken)) {
            throw new ZoomException("Error: _makeCall() | $function - This method requires an authenticated users access token.");
        }

        $paramString = null;

        if (isset($params) && is_array($params)) {
            $paramString = '?' . http_build_query($params);
        }

        $apiCall = self::API_URL . $function . (('GET' === $method) ? $paramString : null);

        $headerData = [
            'Authorization: Bearer ' . $this->getAccessToken()->access_token,
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiCall);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $jsonData = curl_exec($ch);

        if (!$jsonData) {
            throw new ZoomException('Error: _makeCall() - cURL error: ' . curl_error($ch), curl_errno($ch));
        }

        list($headerContent, $jsonData) = explode("\r\n\r\n", $jsonData, 2);

        curl_close($ch);

        return json_decode($jsonData);
    }

    private function _makeOAuthCall($apiHost, $params, $method = 'POST', $header = [])
    {
        $paramString = null;

        if (isset($params) && is_array($params)) {
            $paramString = '?' . http_build_query($params);
        }

        $apiCall = $apiHost . (('GET' === $method) ? $paramString : null);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiCall);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($header, ['Accept: application/json']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_timeout);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $jsonData = curl_exec($ch);

        if (!$jsonData) {
            throw new ZoomException('Error: _makeOAuthCall() - cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($jsonData);
    }

    public function setAccessToken($token)
    {
        $this->_accesstoken = $token;
    }

    public function getAccessToken()
    {
        return $this->_accesstoken;
    }

    public function setAppId($appId)
    {
        $this->_appId = $appId;
    }

    public function getAppId()
    {
        return $this->_appId;
    }

    public function setAppSecret($appSecret)
    {
        $this->_appSecret = $appSecret;
    }

    public function getAppSecret()
    {
        return $this->_appSecret;
    }

    public function setRedirectUri($redirectUri)
    {
        $this->_redirectUri = $redirectUri;
    }

    public function getRedirectUri()
    {
        return $this->_redirectUri;
    }

    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
    }

    public function setConnectTimeout($connectTimeout)
    {
        $this->_connectTimeout = $connectTimeout;
    }
}
