<?php

namespace App\Services\Facebook;

use App\Models\User;
use App\Services\HttpClient;

class Facebook
{
    /**
     * The facebook graph url
     *
     * @var string
     */
    protected $graphUrl = 'https://graph.facebook.com';

    /**
     * The custom http client object
     *
     * @var HttpClient $httpClient
     */
    protected $httpClient;

    /**
     * The facebook graph version
     *
     * @var string
     */
    protected $graphVersion = 'v2.8';

    /**
     * The application ID
     *
     * @var string
     */
    protected $appId = '';

    /**
     * The application secret
     *
     * @var string
     */
    protected $appSecret = '';

    /**
     * The redirect url path
     *
     * @var string
     */
    protected $redirectUrl = '';

    /**
     * The user fields being requested.
     *
     * @var array
     */
    protected $fields = ['name', 'email', 'gender', 'verified', 'link'];

    /**
     * Facebook constructor for setting some vars.
     *
     * @return void
     */
    public function __construct()
    {
        $this->appId       = env('APP_ID', '');
        $this->appSecret   = env('APP_SECRET', '');
        $this->httpClient  = new HttpClient();
        $this->redirectUrl = url('/users/callback');
    }

    /**
     * Generate the redirect url
     *
     * @return array
     */
    protected function getRedirectsParams()
    {
        $params = [
            'client_id'     => $this->appId,
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUrl,
            'scope'         => 'email',
        ];

        return $params;
    }

    /**
     * Generate the redirect url
     *
     * @return string
     */
    protected function getRedirectURL()
    {
        $url = 'https://www.facebook.com/' . $this->graphVersion . '/dialog/oauth?';
        $url .= http_build_query($this->getRedirectsParams());

        return $url;
    }

    /**
     * redirect to the login url
     *
     * @return string $token
     */
    public function redirectToLogin()
    {
        return redirect()->to($this->getRedirectURL());
    }

    /**
     * Get the full access token url
     *
     * @return string
     */
    protected function getAccessTokenUrl()
    {
        return $this->buildGraphUrl(). 'oauth/access_token';
    }

    /**
     * Get the access token parameters
     *
     * @param $code
     *
     * @return array
     */
    protected function getAccessTokenParam($code)
    {
        $params = [
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUrl,
        ];

        return $params;
    }

    /**
     * Get access token from facebook url
     *
     * @param $code
     *
     * @return null
     */
    protected function getAccessToken($code)
    {
        $url  = $this->getAccessTokenUrl();
        $data = json_decode($this->httpClient->run($url, [], 'POST', $this->getAccessTokenParam($code)), true);

        return isset($data['access_token']) ? $data['access_token'] : null;
    }

    /**
     * Build the user data url
     *
     * @param $token
     *
     * @return string
     */
    protected function buildUserDataUrl($token)
    {
        $url = $this->buildGraphUrl() . 'me?access_token=' . $token . '&fields=' . implode(',', $this->fields);

        $appSecretProof = hash_hmac('sha256', $token, $this->appSecret);
        $url .= '&appsecret_proof=' . $appSecretProof;

        return $url;
    }

    /**
     * Get the user's data from facebook api
     *
     * @param $code
     *
     * @return null
     */
    public function getUserData($code)
    {
        $token = $this->getAccessToken($code);
        if (is_null($token)) {
            return $token;
        }

        session(['token' => $token]);
        $url      = $this->buildUserDataUrl($token);
        $response = json_decode($this->httpClient->run($url, ['Accept' => 'application/json']), true);

        return $response;
    }

    /**
     * Building and storing the user object
     *
     * @param $userData
     *
     * @return User
     */
    public function buildUserObject($userData)
    {
        $user            = User::firstOrNew(['email' => $userData['email']]);
        $user->name      = $userData['name'];
        $user->uid       = $userData['id'];
        $user->picture   = $this->buildGraphUrl() . $user->uid . '/picture?width=175';
        $user->is_active = 1;
        $user->token     = session('token');
        $user->provider  = 'facebook';
        $user->save();

        return $user;
    }

    /**
     * Build Facebook Graph url
     *
     * @return string
     */
    protected function buildGraphUrl()
    {
        return $this->graphUrl . '/' . $this->graphVersion . '/';
    }
}
