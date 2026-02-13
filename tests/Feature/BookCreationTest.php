<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_is_created_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', [
            "title" => "Nouveau livre test",
            "author" => "Moi-même",
            "summary" => "Test nouveau livre",
            "isbn" => "1111111111111"
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', ['isbn' => '1111111111111']);
    }

    public function test_book_is_not_created_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', [
            "title" => "T",
            "author" => "Moi",
            "summary" => "Test nouveau livre",
            "isbn" => "2222222222222"
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('books', ['isbn' => '2222222222222']);
    }

    public function test_book_is_not_created_if_not_authenticated(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => 'Nouveau livre pas connecté',
            'author' => 'Auteur pas connecté',
            'summary' => 'Test nouveau livre pas connecté',
            'isbn' => '3333333333333',
        ]);

        $response->assertStatus(401);

        $this->assertDatabaseMissing('books', ['isbn' => '3333333333333']);
    }
}
