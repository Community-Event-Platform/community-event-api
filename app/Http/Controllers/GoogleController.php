<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        try {

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // kiểm tra email đã tồn tại chưa
            $user = User::where('email', $googleUser->getEmail())
                ->first();

            // nếu chưa có => tạo mới
            if (!$user) {

                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                    'role' => 'attendee',
                ]);
            }

            // nếu có nhưng chưa link google
            if (!$user->google_id) {
                $user->google_id = $googleUser->getId();
                $user->save();
            }

            // tạo sanctum token
            $token = $user->createToken('google-token')->plainTextToken;

            // redirect về frontend với cả token và user data
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ];

            return redirect(
                'http://localhost:5173/oauth-success?token=' . $token . '&user=' . urlencode(json_encode($userData))
            );

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Google login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}