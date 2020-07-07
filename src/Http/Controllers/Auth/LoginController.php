<?php

namespace TulsaPublicSchools\SnipeItCustomizations\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{

    /**
     * Redirect the user to the Microsoft Graph authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGraphProvider()
    {
        return Socialite::with('graph')->setTenantId(env('GRAPH_TENANT_ID', 'common'))->redirect();
    }

    /**
     * Obtain the user information from Microsoft Graph.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGraphProviderCallback()
    {
        $socialite = Socialite::with('graph')->setTenantId(env('GRAPH_TENANT_ID', 'common'))->user();

        // TODO: should we include deleted_at users?
        $user = User::where('email', '=', $socialite->getEmail())->first();

        if ($user && ($user->deleted_at || !$user->activated)) {
            // TODO: maybe we do something different
            //       - un-delete?
            //       - re-activate?
            return view('errors.403');
        }

        if (!$user) {
            $user = $this->registerGraphUser($socialite);

            if ($user->getErrors()->count()) {
                Log::error('Error registering user from Microsoft Graph login', $user->getErrors()->toArray());

                return view('errors.500');
            }
        }

        Auth::login($user, true);

        if ($user = Auth::user()) {
            $user->last_login = \Carbon::now();
            $user->save();
        }

        return redirect()->intended()->with('success', trans('auth/message.signin.success'));
    }

    private function registerGraphUser($socialite) {
        $email = $socialite->getEmail();

        $name = preg_replace('/\s+/', ' ', trim($socialite->getName()));
        list($first_name, $last_name) = explode(' ', $name, 2);

        $user = new User;

        $user->first_name = $first_name;
        $user->last_name = $last_name ?: $first_name;
        $user->username = $email;
        $user->email = $email;
        $user->password = bcrypt(str_random(20));
        $user->save();

        return $user;
    }
}
