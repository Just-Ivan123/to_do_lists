<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
       return $this->authService->login($request);

    }

    public function register(Request $request){
       return $this->authService->register($request);
    }

    public function logout()
    {
        return $this->authService->logout();
    }

    public function refresh()
    {
       return $this->authService->refresh();
    }

    public function checkAuthentication(Request $request)
    {
        return $this->authService->checkAuthentication($request);
    }
}