<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use OpenApi\Annotations as OA;

class BookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/books",
     *     summary="Liste paginée des livres",
     *     description="Récupère une liste de livres avec pagination (2 par page). Endpoint public",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string", example="application/json")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             example={
     *               "data": {
     *                 {"title": "Harry Potter", "author": "J.K Rowling", "summary": "Un jeune sorcier...", "isbn": "1234567890123", "_links": { "self": "http://localhost:8000/api/v1/books/1", "update": "http://localhost:8000/api/v1/books/1", "delete": "http://localhost:8000/api/v1/books/1","all": "http://localhost:8000/api/v1/books" }},
     *               },
     *               "links": {"first":"...", "last":"...", "prev":null, "next":"..."},
     *               "meta": {"current_page": 1, "from": 1, "lastpage": 3, "links":  { "url": null, "label": "&laquo; Previous", "page": null, "active": false }, { "url": "http://localhost:8000/api/v1/books?page=1", "label": "1", "page": 1, "active": true }, { "url": "http://localhost:8000/api/v1/books?page=2", "label": "2", "page": 2, "active": false }, { "url": "http://localhost:8000/api/v1/books?page=3", "label": "3", "page": 3, "active": false }, { "url": "http://localhost:8000/api/v1/books?page=2", "label": "Next &raquo;", "page": 2, "active": false } ,"path": "http://localhost:8000/api/v1/books", "per_page": 2, "to": 2, "total": 6}
     *             }
     *         )
     *     )
     * )
     */
    public function index()
    {
        $books = Book::orderBy('id', 'asc')->paginate(2);
        return BookResource::collection($books);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/books",
     *     summary="Créer un livre",
     *     description="Crée un livre. Il faut être connecté",
     *     tags={"Books"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", example="application/json")),
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer {token}",
     *         @OA\Schema(type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOi...")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","author","summary","isbn"},
     *             @OA\Property(property="title", type="string", example="Harry Potter"),
     *             @OA\Property(property="author", type="string", example="J.K Rowling"),
     *             @OA\Property(property="summary", type="string", example="Un jeune sorcier découvre ses pouvoirs..."),
     *             @OA\Property(property="isbn", type="string", example="1234567890123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Livre créé",
     *         @OA\JsonContent(
     *             example={"data":{"id":1,"title":"Harry Potter","author":"J.K Rowling","summary":"Un jeune sorcier...","isbn":"1234567890123"}}
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(example={"message":"Unauthenticated."})
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur validation",
     *         @OA\JsonContent(example={"message":"The title field is required.","errors":{"title":{"The title field is required."}}})
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/books/{book}",
     *     summary="Afficher un livre",
     *     description="Affiche le livre qui correspond à l'ID. Endpoint public",
     *     tags={"Books"},
     *     @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", example="application/json")),
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         description="ID du livre",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(example={"data":{"id":1,"title":"Harry Potter","author":"J.K Rowling","summary":"Un jeune sorcier...","isbn":"1234567890123"}})
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livre non trouvé",
     *         @OA\JsonContent(example={"message":"Not Found"})
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/v1/books/{book}",
     *     summary="Mettre à jour un livre",
     *     description="Met à jour un livre. Il faut être connecté",
     *     tags={"Books"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", example="application/json")),
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer {token}",
     *         @OA\Schema(type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOi...")
     *     ),
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         description="ID du livre",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","author","summary","isbn"},
     *             @OA\Property(property="title", type="string", example="Nouveau titre"),
     *             @OA\Property(property="author", type="string", example="Nouvel auteur"),
     *             @OA\Property(property="summary", type="string", example="Résumé mis à jour"),
     *             @OA\Property(property="isbn", type="string", example="1234567890123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Livre mis à jour",
     *         @OA\JsonContent(example={"data":{"id":1,"title":"Nouveau titre","author":"Nouvel auteur","summary":"Résumé mis à jour","isbn":"1234567890123"}})
     *     ),
     *     @OA\Response(response=401, description="Non authentifié", @OA\JsonContent(example={"message":"Unauthenticated."})),
     *     @OA\Response(response=404, description="Non trouvé", @OA\JsonContent(example={"message":"Not Found"})),
     *     @OA\Response(response=422, description="Erreur validation", @OA\JsonContent(example={"message":"The title field is required.","errors":{"title":{"The title field is required."}}}))
     * )
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
     * @OA\Delete(
     *     path="/api/v1/books/{book}",
     *     summary="Supprimer un livre",
     *     description="Supprime un livre. Il faut être connecté",
     *     tags={"Books"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", example="application/json")),
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer {token}",
     *         @OA\Schema(type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOi...")
     *     ),
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         description="ID du livre",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="Supprimé"),
     *     @OA\Response(response=401, description="Non authentifié", @OA\JsonContent(example={"message":"Unauthenticated."})),
     *     @OA\Response(response=404, description="Non trouvé", @OA\JsonContent(example={"message":"Not Found"}))
     * )
     */

    public function destroy(Book $book)
    {

        Cache::forget("book:{$book->id}");
        $book->delete();
        return response()->noContent();
    }
}
