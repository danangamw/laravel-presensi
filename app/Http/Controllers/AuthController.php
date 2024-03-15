<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function processLogin(Request $req)
    {
        if (Auth::guard('karyawan')->attempt(['nik' => $req->nik, 'password' => $req->password])) {
            return redirect("/dashboard");
        } else {
            return redirect('/')->with(['warning' => 'Nik atau Password yang anda masukkan salah']);
        }
    }

    public function processLogout()
    {
        if (Auth::guard('karyawan')->check()) {
            Auth::guard('karyawan')->logout();
            return redirect('/');
        }
    }
}
