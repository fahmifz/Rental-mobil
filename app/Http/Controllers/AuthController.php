<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForUser(Auth::user());
        }

        return view('pages.auth.signin', ['title' => 'Login']);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirectForUser($request->user());
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForUser(Auth::user());
        }

        return view('pages.auth.signup', ['title' => 'Daftar Akun']);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->role = 'pelanggan';
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('account')
            ->with('success', 'Akun berhasil dibuat.');
    }

    public function account(Request $request): View
    {
        return view('pages.account', ['user' => $request->user()]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Anda telah keluar dari akun.');
    }

    private function redirectForUser(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->route('account');
    }
}
