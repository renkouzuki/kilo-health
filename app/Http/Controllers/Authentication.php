<?php

namespace App\Http\Controllers;

use App\CustomValidate\CustomValid;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Authentication extends Controller
{
    private Request $req;

    public function __construct(Request $req)
    {
        $this->req = $req;
    }

    public function register()
    {
        try {
            $validated = Validator::make($this->req->all(),[
                'name'=>'required|string|max:255',
                'email'=>'required|email|string|unique:users',
                'password'=>'required|string|max:8|confirmed'
            ],CustomValid::registerMsg())->validate();


            $user = User::create([
                'name'=>$validated['name'],
                'email'=>$validated['email'],
                'password'=>Hash::make($validated['password'])
            ]);

            $defaultRole = Role::where('name' , 'user')->first();

            if($defaultRole){
                $user->role_id = $defaultRole->pluck('id');
                $user->save();
            }

            return response()->json(['success'=>true , 'msg'=>'successfully'] , 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success'=>false,
                'msg'=>'oops maybe u forgot something in the required input fields :D',
                'issue'=>$e->errors()
            ],422);
        } catch (Exception $e) {
            return response()->json([
                'success'=>false,
                'msg'=>'something went wrong',
                'issue'=>$e->getMessage()
            ],500);
        }
    }

    public function login()
    {
        try {
            $validated = Validator::make($this->req->all(),[
                
            ])->validate();
        } catch (ValidationException $e) {
        } catch (Exception $e) {
        }
    }

    public function logout() {
        $this->req->user()->currentUserToken()->delete();
        return response()->json([
            'success'=>true,
            'msg'=>'logout successfully'
        ]);
    }

    public function alluser() {}
}
