<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'author' => mb_strtoupper($this->author),
            'summary' => $this->summary,
            'isbn' => $this->isbn,

            '_links' => [
                'self' => route('books.show', $this->resource),
                'update' => route('books.update', $this->resource),
                'delete' => route('books.destroy', $this->resource),
                'all' => route('books.index'),
            ],
        ];
    }
}
