<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class FirebaseService
{
    protected $factory;
    protected $auth;
    protected $firestore;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase_credentials.json'));

        $this->auth = $factory->createAuth();
        $this->firestore = $factory->createFirestore()->database();
    }

    public function getAuth()
    {
        return $this->auth;
    }
    public function auth()
    {
        return $this->auth;
    }

    public function firestore()
    {
        return $this->firestore;
    }

    public function createUser($email, $password, $displayName)
    {
        return $this->auth->createUser([
            'email' => $email,
            'password' => $password,
            'displayName' => $displayName,
        ]);
    }

    public function signInWithEmailAndPassword($email, $password)
    {
        $apiKey = config('services.firebase.api_key'); // OR hardcode the API key
        $response = \Illuminate\Support\Facades\Http::post(
            "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$apiKey}",
            [
                'email' => $email,
                'password' => $password,
                'returnSecureToken' => true,
            ]
        );

        if ($response->successful()) {
            return $response->json(); // 🔁 returns array!
        }

        throw new \Exception('Firebase login failed');
    }

    public function getTriviaByLandmarkId($landmarkId)
    {
        return $this->firestore
            ->collection('landmarks')
            ->document($landmarkId)
            ->collection('trivia')
            ->documents();
    }
}
