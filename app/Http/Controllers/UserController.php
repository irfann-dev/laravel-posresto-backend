<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    //index
    public function index(Request $request)
    {
        // get all users with pagination
        $users = DB::table('users')
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', '%' . $name . '%')
                      ->orWhere('email', 'like', '%' . $name . '%');
            })
            ->paginate(10);

        return view('pages.user.index', compact('users'));
    }

    //create
    public function create()
    {
        return view('pages.user.create');
    }

    //store
    public function store(Request $request)
    {
        //validate the request
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,staff,user', // Perbaikan validasi role
        ]);

        // Store the requested
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password); // Hash the password
        $user->role = $request->role;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    //show
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('pages.user.show', compact('user'));
    }

    //edit
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('pages.user.edit', compact('user'));
    }

    //update
    public function update(Request $request, $id)
    {
        //validate the request
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|in:admin,staff,user', // Perbaikan validasi role
        ]);

        // Find user by id and update the data
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Update password if it's provided
        if ($request->password) {
            $user->password = Hash::make($request->password); // Hash the new password
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    //destroy
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
