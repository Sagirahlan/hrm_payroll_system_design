<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\AuditTrail;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load('employee.department', 'employee.cadre', 'employee.gradeLevel', 'employee.nextOfKin', 'employee.biometricData', 'employee.bank', 'employee.state', 'employee.lga', 'employee.ward', 'employee.rank', 'employee.step');
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_profile'], ['only' => ['show', 'edit']]);
        $this->middleware(['permission:edit_profile'], ['only' => ['update']]);
        $this->middleware(['permission:change_password'], ['only' => ['destroy']]);
    }

    public function show()
    {
        $user = Auth::user()->load('employee.department', 'employee.cadre', 'employee.gradeLevel', 'employee.nextOfKin', 'employee.biometricData', 'employee.bank', 'employee.state', 'employee.lga', 'employee.ward', 'employee.rank', 'employee.step');
        return view('profile.show', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated_profile',
            'description' => 'User updated their own profile.',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'User', 'entity_id' => Auth::id()]),
        ]);

        return Redirect::route('profile.show')->with('status', 'profile-updated');
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
