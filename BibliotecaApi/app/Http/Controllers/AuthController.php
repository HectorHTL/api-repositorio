<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(),
        [

        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|confirmed'
        ]);

        if(!$validator->fails()){
            DB::beginTransaction();
            try {
                //Validate request data
                $request->validate([
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|confirmed'
                ]);
                //Set data
                $user = new User();
                $user->name = $request->name;
                $user->email = $this->removeWithSpace($request->email) ;
                $user->password = Hash::make($request->password);
                $user->save();
                DB::commit();
                return $this->getResponse201('user account', 'created', $user);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        }else{
            return $this->getResponse500([$validator->errors()]);

        }

    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (!$validator->fails()) {
           $user = User::where('email','=',$request->email)->first();
           if(isset($user->id)){
            if(Hash::check($request->password,$user->password)){
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'Successful authentication',
                    'access_token' => $token,
                ],200);
            }else{
                return $this->getResponse401();
            }
           }else{
            return $this->getResponse401();
           }
        }else{
            return $this->getResponse500([$validator->errors()]);
        }

    }



    public function userProfile()
    {

        return $this->getResponse200(auth()->user());
    }

    /*

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); //Revoke all tokens
        return response()->json([
            'message' => "Logout successful"
        ], 200);
    }

    */

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete(); //Revoke current token
        return response()->json([
            'message' => "Logout successful"
        ], 200);
    }




    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'password' => 'required|confirmed'
        ]);


        if(!$validator->fails()){

            DB::beginTransaction();

            try{

                $user = new User();
                $user = auth()->user();
                auth()->user()->tokens()->delete();
                $user->password = Hash::make($request->password);
                $user->update();
                DB::commit();
                return $this->getResponse201('password', 'updated', []);

            }catch(Exception $e){
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        }else{
            return $this->getResponse500([$validator->errors()]);
        }


    }


}
