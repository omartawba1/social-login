<?php

namespace App\Http\Controllers;

use App\Services\Facebook\Facebook;

class UsersController
{
    /**
     *The facebook service object
     *
     * @var Facebook
     */
    private $facebook;

    /**
     * UsersController constructor.
     *
     * @param Facebook $facebook
     *
     * @return void
     */
    public function __construct(Facebook $facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * The login method that redirects to facebook login
     *
     * @return response
     */
    public function login()
    {
        return $this->facebook->redirectToLogin();
    }

    /**
     * The facebook callback method
     *
     * @return resource
     */
    public function callback()
    {
        $userData = $this->facebook->getUserData(request('code'));
        if (is_null($userData)) {
            return redirect()->to('/users/login');
        }
        $user = $this->facebook->buildUserObject($userData);

        auth()->login($user);

        return redirect('/users/dashboard');
    }

    /**
     * The logout method
     *
     * @return response
     */
    public function logout()
    {
        $user            = auth()->user();
        $user->is_active = 0;
        $user->save();
        session()->flush();

        return redirect('/');
    }

    /**
     * The dashboard method
     *
     * @return response
     */
    public function dashboard()
    {
        return view('users.dashboard');
    }
}
