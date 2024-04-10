<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

use Carbon\Carbon;
use App\Mail\SendMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\General\GeneralEmailController;

class AuthController extends Controller {

    //*** Consultar la información del usuario logeado ***//

    public function profile() {
        
        $user = User::find($this->userLoggedInId());
        
        if (empty($user)) {
            return [
                "message" => "El usuario no se encuentra en el sistema",
                "response_code" => 200,
            ];
        } else {

            $infoUser = array();

            array_push($infoUser, array(
                "name" => $user->name,
                "roleName" => $user->role->name,
                "userId" => $user->id,
                "rolId" => $user->role->id
            ));

            return $infoUser;
        }
    }

    public function reqForgotPassword(Request $request){
        if(!$this->validEmail($request->email)) {
            return response()->json([
                'message' => 'Email not found.'
            ], Response::HTTP_NOT_FOUND);
        } else {
            $this->sendEmail($request->email);
            return response()->json([
                'message' => 'Password reset mail has been sent.'
            ], Response::HTTP_OK);            
        }
    }


    public function sendEmail($email){
        $token = $this->createToken($email);
        // Mail::to($email)->send(new SendMail($token));

        $emailController = new GeneralEmailController();

        //--- Parametros para la funcion email ---//

        $view = "emails.forgot_password";

        $info = array(
            "email" => $email,
            "subject" => "Change password",
            "token" => $token
        );
        $emailController->sendEmail($view, $info);
    }

    public function validEmail($email) {
       return !!User::where('email', $email)->first();
    }

    public function createToken($email){
      $isToken = DB::table('password_resets')->where('email', $email)->first();

      if($isToken) {
        return $isToken->token;
      }

      $token = Str::random(80);;
      $this->saveToken($token, $email);
      return $token;
    }

    public function saveToken($token, $email){
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()            
        ]);
    }

    public function updatePassword(Request $request){
        return $this->validateToken($request)->count() > 0 ? $this->changePassword($request) : $this->noToken();
    }

    private function validateToken($request){
        return DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->passwordToken
        ]);
    }

    private function noToken() {
        return response()->json([
          'error' => 'Email or token does not exist.'
        ],Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function changePassword($request) {
        $user = User::whereEmail($request->email)->first();
        $user->update([
          'password'=>bcrypt($request->password)
        ]);
        $this->validateToken($request)->delete();
        return response()->json([
          'data' => 'Password changed successfully.'
        ],Response::HTTP_CREATED);
    }  
}
