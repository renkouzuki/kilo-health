<?php

namespace App\Http\Controllers\Auth;

use App\CustomValidation\CustomValue;
use App\Events\UserMangement\UserInfoUpdated;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class authenticate extends Controller
{
    private Request $req;
    public function __construct(Request $req)
    {
        $this->req = $req;
    }
    public function register()
    {
        try {
            $validated = Validator::make($this->req->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed'
            ])->validate();

            $defaultId = Role::where('name', 'user')->first()->id;

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $defaultId
            ]);

            if (!$user->sendOTP('verification')) {
                return response()->json([
                    'message' => 'Failed to send OTP email'
                ], 500);
            }

            $expireDate = now()->addDays(7);
            $token = $user->createToken('my_token', expiresAt: $expireDate)->plainTextToken;

            return response()->json(['success' => true, 'message' => 'welcome new member ^w^', 'data' => $user, 'token' => $token], 201);
        } catch (ValidationException $e) {
            $customErrorMessage = 'Oops, looks like something went wrong with your submission.';
            return response(['success' => false, 'message' => $customErrorMessage, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error("error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function login()
    {
        try {
            $validated = Validator::make($this->req->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ], CustomValue::LoginMsg())->validate();

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response(['success' => false, 'message' => "incorrect credential! >w<"]);
            }

            if (!$user->email_verified_at) {
                return response()->json([
                    'message' => 'Please verify your email first'
                ], 403);
            }

            $expireDate = now()->addDays(7);
            $token = $user->createToken('my_token', expiresAt: $expireDate)->plainTextToken;

            return response()->json(['success' => true, 'message' => 'welcome back master :3', 'data' => $user, 'token' => $token], 200);
        } catch (ValidationException $e) {
            $customErrorMessage = 'oops look likes something wrong with your submission';
            return response(['success' => false, 'message' => $customErrorMessage, 'issues' => $e->errors()], 422);
        } catch (Exception $e) {
            Log("error: ", $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getUser()
    {
        try {
            return response()->json(['success' => true, 'data' => $this->req->user()], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function logout()
    {

        $this->req->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully!']);
    }

    public function updateUserInfo()
    {
        try {
            $this->req->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email',
                'avatar' => 'sometimes|file|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = User::findOrFail($this->req->user()->id);

            $data = $this->req->only(['name', 'email']);

            if ($this->req->hasFile('avatar')) {
                if ($user->avatar) {
                    Storage::disk('s3')->delete($user->avatar);
                }
                $user->avatar = $this->req->file('avatar')->store('avatar', 's3');
            }

            $user->update($data);

            event(new UserInfoUpdated($user));

            return response()->json(['success' => true, 'message' => 'User updated successfully', 'data' => $user], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function changePassword()
    {
        $this->req->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);
        $user = $this->req->user();
        try {
            if (!Hash::check($this->req->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Your current password does not match our records.'], 422);
            }

            $user->password = Hash::make($this->req->new_password);

            $user->save();

            return response()->json(['success' => true, 'message' => 'Password changed successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
