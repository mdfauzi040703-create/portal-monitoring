<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    public function index() {
        return response()->json(User::select('id','name','email','whatsapp','role','atasan_id')->get());
    }

    public function store(Request $request) {
    $request->validate([
        'name'     => 'required|string',
        'email'    => 'required|email|unique:users',
        'password' => 'required|min:6',
        'role'     => 'required|in:admin,pic,manager,asisten_manager',
    ]);

    $user = User::create([
        'name'      => $request->name,
        'email'     => $request->email,
        'password'  => \Illuminate\Support\Facades\Hash::make($request->password),
        'whatsapp'  => $request->whatsapp,
        'role'      => $request->role,
        'atasan_id' => $request->atasan_id,
    ]);

    return response()->json($user, 201);
}
    public function destroy(User $user) {
        $user->delete();
        return response()->json(['message' => 'User dihapus']);
    }
}