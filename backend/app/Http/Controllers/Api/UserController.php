<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/users
     * Admin: list semua user dengan search dan pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::where('role', 'user')
            ->when($request->search, fn ($q) =>
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->withCount('orders')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return $this->successResponse(
            UserResource::collection($users)->response()->getData(true),
            'Data pengguna berhasil diambil.'
        );
    }

    /**
     * GET /api/users/{user}
     * Detail user.
     */
    public function show(User $user): JsonResponse
    {
        $user->loadCount('orders');

        return $this->successResponse(
            new UserResource($user),
            'Detail pengguna berhasil diambil.'
        );
    }

    /**
     * PUT /api/users/{user}
     * Admin edit data user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $this->successResponse(
            new UserResource($user),
            'Data pengguna berhasil diperbarui.'
        );
    }

    /**
     * DELETE /api/users/{user}
     * Admin hapus user.
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->role === 'admin') {
            return $this->errorResponse('Akun admin tidak dapat dihapus.', 422);
        }

        $user->delete();

        return $this->successResponse(null, 'Pengguna berhasil dihapus.');
    }
}
