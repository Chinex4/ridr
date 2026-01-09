<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserStoreRequest;
use App\Http\Requests\AdminUserUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * @group Admin
     */
    public function index()
    {
        $users = User::query()->orderByDesc('created_at')->paginate(20);

        return response()->json($users);
    }

    /**
     * @group Admin
     */
    public function store(AdminUserStoreRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json(['user' => $user], 201);
    }

    /**
     * @group Admin
     */
    public function show(User $user)
    {
        return response()->json(['user' => $user->load('driver')]);
    }

    /**
     * @group Admin
     */
    public function update(AdminUserUpdateRequest $request, User $user)
    {
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json(['user' => $user->fresh()]);
    }

    /**
     * @group Admin
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
