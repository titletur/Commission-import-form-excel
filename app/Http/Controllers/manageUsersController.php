<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class manageUsersController extends Controller
{

    public function index()
    {

        $users = User::whereNull('status_user')
                        ->orderBy('id', 'DESC')
                        ->get();
        return view('users.index', compact('users'));
        
    }

    public function store(Request $request)
    {
        try {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'department' => ['required', 'string', 'max:50'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $user = User::updateOrCreate([
            'email' => $request->email,
        ],
        [
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'department' => $request->department,
            'permissions' => json_encode($request->input('permissions', [])),
            
        ]);

        event(new Registered($user));

        return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
        // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
        return redirect()->route('users.index')->withErrors(['error' => 'Failed to Create User: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'department' => ['required', 'string', 'max:50'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);
    
        $user = User::findOrFail($id);
        // $user->update($request->all());
        $user->name = $request->input('name');
        $user->department = $request->input('department');
        $user->permissions = json_encode($request->input('permissions', []));
        $user->save();
    
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('users.index')->withErrors(['error' => 'Failed to update User: ' . $e->getMessage()]);
        }
    }
    public function updateStatus(Request $request, $id)
    {
        try {
            $User = User::findOrFail($id);
            $User->status_user = 'deleted'; 
            $User->save();

            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            // จับข้อผิดพลาดแล้วส่งกลับพร้อมกับข้อความ error
            return redirect()->route('users.index')->withErrors(['error' => 'Failed to Delete User: ' . $e->getMessage()]);
        }
    }
}
