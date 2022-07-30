<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index(){
        $users = app("db")->table('users')->select('id', 'fullname', 'username', 'email')->get();
        return response()->json($users,200);
    }
    public function create(Request $request){
        // return $request->all();
       try {
        $this->validate($request, [
            'fullname' => 'required',
            'username' => 'required|unique:users',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
       } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage()
            ],422);  
       }

      try {
        $id = app('db')->table('users')->insertGetId([
            'fullname' => trim($request->input('fullname')),
            'username' => strtolower(trim($request->input('username'))),
            'email'    => strtolower(trim($request->input('email'))),
            'password' => app('hash')->make($request->input('password'))
       ]);

       $user = app('db')->table('users')->select('fullname', 'username','email')->where('id', $id)->first();

       return response()->json([
        'id'       => $id,
        'fullname' => $user->fullname,
        'username' => $user->username,
        'email'    => $user->email,
       ],201);

      } catch (\PDOException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 403);
      }
    }
}
