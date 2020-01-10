<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\auth;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $req)
    {
        $user = new User;

        $user->name = $req->input('name');
        $user->email = $req->input('email');
        $user->password = md5($req->input('password'));

        $user->save();

        return response("Register Done", 200);
    }

    public function login(Request $req)
    {
        $uname = $req->input('uname');
        $upass = $req->input('upass');
        $data = User::where('email', $uname)->where('password', md5($upass))->get();
        //echo $data;
        if (count($data) <= 0) {
            return response("Not Register.Data Not Found.", 404);
        } else {

            $id = $data[0]->id;
            $time = Carbon::now()->timestamp;
            $random = rand(100000, 999999);
            $authtoken = $id.$time.$random;
            echo $authtoken;
            $auth = new auth;
            $auth->authToken = $authtoken;
            $auth->userid = $id;
            $auth->save();
            return response("Log in", 200);
        }
    }

    public function chnagepass(Request $req)
    {
        $token = $req->input('token');
        $acheck = auth::where('authToken', $token)->get();
        //echo $acheck;
        if (count($acheck) <= 0) {
            return response("You Are Not Log In Yet.", 404);
        } else {
            $cpass = User::select("password")->where('id', $acheck[0]->userid)->first();
            //echo $cpass;
            $current_pass = $req->input('current_pass');
            $check = strcasecmp(md5($current_pass), $cpass['password']);
            if ($check == 0) {
                $npass = $req->input("new_pass");
                $cnpass = $req->input("cnew_pass");
                if (strcasecmp($npass, $cnpass) == 0) {
                    $id = User::where('id', $acheck[0]->userid)->update(['password'=>md5($npass)]);

                    return response("Password Changed.", 200);
                } else {
                    return response("New Password & Confirm Password Not Match.", 404);
                }
            } else {
                return response("Wrong Current Password.", 404);
            }
        }
    }

    public function logout(Request $req)
    {
        $token = $req->input('token');
        $data = auth::where('authtoken', $token)->delete();
        return response("Loged Out.", 200);
    }
}
