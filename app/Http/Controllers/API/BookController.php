<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::orderBy('id', 'asc')->paginate(2);
        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'min:3', 'max:100'],
            'summary' => ['required', 'string', 'min:10', 'max:500'],
            'isbn' => ['required', 'string', 'size:13', 'unique:books,isbn'],
        ]);

        $book = Book::create($validated);

        return new BookResource($book);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $cacheKey = "book:{$book->id}";

        $bookData = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($book) {
            return Book::findOrFail($book->id);
        });

        return new BookResource($bookData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'min:3', 'max:100'],
            'summary' => ['required', 'string', 'min:10', 'max:500'],
            'isbn' => [
                'required',
                'string',
                'size:13',
                Rule::unique('books', 'isbn')->ignore($book->id),
            ],
        ]);

        Cache::forget("book:{$book->id}");
        $book->update($validated);
        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {

        Cache::forget("book:{$book->id}");
        $book->delete();
        return response()->noContent();
    }
}
