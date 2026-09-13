<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Obtener estadísticas del usuario con validaciones de seguridad
        $noticiasCreadas = $user->noticias()->count();
        $noticiasPublicadas = $user->noticias()->where('publicada', true)->count();
        
        $stats = [
            'noticias_creadas' => $noticiasCreadas,
            'noticias_publicadas' => $noticiasPublicadas,
            'dias_activo' => $user->created_at ? $user->created_at->diffInDays(now()) : 0,
            'ultimo_acceso' => $user->updated_at ? $user->updated_at->diffForHumans() : 'Nunca',
        ];
        
        return view('admin.profile.index', compact('user', 'stats'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('admin.profile.index')->with('success', 'Perfil actualizado exitosamente.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return Redirect::route('admin.profile.index')->with('success', 'Contraseña actualizada exitosamente.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}