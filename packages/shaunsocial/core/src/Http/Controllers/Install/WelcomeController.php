<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Install;

use Illuminate\Routing\Controller;

class WelcomeController extends Controller
{
    /**
     * Display the installer welcome page.
     *
     * @return \Illuminate\Http\Response
     */
    public function welcome()
    {
        return view('shaun_core_install::installe.welcome');
    }
}
