<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\AuditTrail;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_profile'], ['only' => ['show']]);
        $this->middleware(['permission:change_password'], ['only' => ['changePassword', 'updatePassword']]);
    }

    public function show()
    {
        $user = Auth::user()->load('employee.department', 'employee.cadre', 'employee.gradeLevel', 'employee.nextOfKin', 'employee.biometricData', 'employee.bank', 'employee.state', 'employee.lga', 'employee.ward', 'employee.rank', 'employee.step');
        return view('profile.show', compact('user'));
    }

    /**
     * Show the change password form.
     */
    public function changePassword(): View
    {
        return view('profile.change-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password_hash' => Hash::make($request->password),
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated_password',
            'description' => 'User changed their password.',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'User', 'entity_id' => Auth::id()]),
        ]);

        // Log the user out after password change for security
        Auth::logout();

        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login with success message
        return Redirect::to('/login')->with('success', 'Your password has been updated successfully. Please login with your new password.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        AuditTrail::create([
            'user_id' => $user->id,
            'action' => 'deleted_profile',
            'description' => 'User deleted their own account.',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'User', 'entity_id' => $user->id]),
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
