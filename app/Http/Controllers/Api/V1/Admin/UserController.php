<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $query->where(function ($q) use ($request): void {
                    $q->where('name', 'like', "%{$request->string('q')}%")
                        ->orWhere('email', 'like', "%{$request->string('q')}%");
                });
            })
            ->latest()
            ->paginate((int) $request->input('per_page', 20))
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', 'string', Rule::in(['customer', 'admin'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'url'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data = $request->only(['name', 'email', 'phone', 'avatar', 'role']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->string('password'));
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User updated.',
        ], Response::HTTP_OK);
    }

    public function toggleBlock(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'blocked' => ['required', 'boolean'],
        ]);

        $user->update([
            'blocked' => (bool) $request->boolean('blocked'),
            'blocked_at' => $request->boolean('blocked') ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User status updated.',
        ], Response::HTTP_OK);
    }
}
