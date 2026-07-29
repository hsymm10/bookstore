<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/books
     * List buku dengan search, filter kategori, dan pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $books = Book::with('category')
            ->when($request->search, fn ($q) => $q->where('nama_buku', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->category_slug, fn ($q) => $q->whereHas('category', fn ($cq) =>
                $cq->where('slug', $request->category_slug)
            ))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return $this->successResponse(
            BookResource::collection($books)->response()->getData(true),
            'Data buku berhasil diambil.'
        );
    }

    /**
     * POST /api/books
     * Tambah buku baru dengan upload gambar.
     */
    public function store(BookRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('books', 'public');
        }

        $book = Book::create($data);
        $book->load('category');

        return $this->successResponse(
            new BookResource($book),
            'Buku berhasil ditambahkan.',
            201
        );
    }

    /**
     * GET /api/books/{book}
     * Detail buku.
     */
    public function show(Book $book): JsonResponse
    {
        $book->load('category');

        return $this->successResponse(
            new BookResource($book),
            'Detail buku berhasil diambil.'
        );
    }

    /**
     * PUT /api/books/{book}
     * Edit buku.
     */
    public function update(BookRequest $request, Book $book): JsonResponse
    {
        $data = $request->validated();

        // Upload gambar baru jika ada
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($book->gambar) {
                Storage::disk('public')->delete($book->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('books', 'public');
        }

        // Hapus key gambar dari data jika tidak ada file baru (agar tidak overwrite dengan null)
        if (! $request->hasFile('gambar')) {
            unset($data['gambar']);
        }

        $book->update($data);
        $book->load('category');

        return $this->successResponse(
            new BookResource($book),
            'Buku berhasil diperbarui.'
        );
    }

    /**
     * DELETE /api/books/{book}
     * Hapus buku.
     */
    public function destroy(Book $book): JsonResponse
    {
        // Cek apakah stok masih tersedia
        if ($book->stok > 0) {
            return $this->errorResponse(
                'Buku tidak dapat dihapus karena stok masih tersedia. Silakan kosongkan stok terlebih dahulu.',
                422
            );
        }

        // Cek apakah buku sudah pernah dipesan
        if ($book->orderDetails()->exists()) {
            return $this->errorResponse(
                'Buku tidak dapat dihapus karena sudah ada pesanan.',
                422
            );
        }

        // Hapus gambar dari storage
        if ($book->gambar) {
            Storage::disk('public')->delete($book->gambar);
        }

        $book->delete();

        return $this->successResponse(null, 'Buku berhasil dihapus.');
    }
}
