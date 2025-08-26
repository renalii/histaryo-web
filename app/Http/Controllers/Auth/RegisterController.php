<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Kreait\Firebase\Exception\Auth\EmailExists;

class RegisterController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'display_name' => 'required|string',
            'role' => 'required|in:admin,curator',
        ]);

        $email = $request->email;
        $password = $request->password;
        $displayName = $request->display_name;
        $role = $request->role;

        try {
            $user = $this->firebase->createUser($email, $password, $displayName);
            $uid = $user->uid;

            $this->firebase->getAuth()->setCustomUserClaims($uid, ['role' => $role]);

            $this->firebase->firestore()
                ->collection('users')
                ->document($uid)
                ->set([
                    'email' => $email,
                    'display_name' => $displayName,
                    'role' => $role,
                    'created_at' => now()->toDateTimeString(),
                ]);

            return redirect()->route('login')->with('success', 'Registration successful! Please log in.');

        } catch (EmailExists $e) {
            return back()->withErrors(['error' => 'The email is already registered. Please use a different one.'])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Registration failed. ' . $e->getMessage()])->withInput();
        }
    }
}
