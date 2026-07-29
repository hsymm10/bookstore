<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/categories
     * List semua kategori (public + paginated untuk admin).
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::withCount('books')
            ->when($request->search, fn ($q) => $q->where('nama', 'like', "%{$request->search}%"))
            ->orderBy('nama')
            ->paginate($request->per_page ?? 10);

        return $this->successResponse(
            CategoryResource::collection($categories)->response()->getData(true),
            'Data kategori berhasil diambil.'
        );
    }

    /**
     * GET /api/categories/all
     * List semua kategori tanpa pagination (untuk dropdown).
     */
    public function all(): JsonResponse
    {
        $categories = Category::withCount('books')->orderBy('nama')->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Data kategori berhasil diambil.'
        );
    }

    /**
     * POST /api/categories
     * Tambah kategori baru.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create(['nama' => $request->nama]);

        return $this->successResponse(
            new CategoryResource($category),
            'Kategori berhasil ditambahkan.',
            201
        );
    }

    /**
     * GET /api/categories/{category}
     * Detail kategori.
     */
    public function show(Category $category): JsonResponse
    {
        $category->loadCount('books');

        return $this->successResponse(
            new CategoryResource($category),
            'Detail kategori berhasil diambil.'
        );
    }

    /**
     * PUT /api/categories/{category}
     * Edit kategori.
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category->update(['nama' => $request->nama]);

        return $this->successResponse(
            new CategoryResource($category),
            'Kategori berhasil diperbarui.'
        );
    }

    /**
     * DELETE /api/categories/{category}
     * Hapus kategori.
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->books()->exists()) {
            return $this->errorResponse(
                'Kategori tidak dapat dihapus karena masih memiliki buku.',
                422
            );
        }

        $category->delete();

        return $this->successResponse(null, 'Kategori berhasil dihapus.');
    }
}
