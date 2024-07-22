<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;

class CategoryControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushall(); // Limpiar Redis antes de cada prueba
    }

    #[Test]
    public function it_can_create_a_category()
    {
        $response = $this->postJson('/api/categories', [
            'name' => 'Electronics',
            'parent_id' => 'root'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id']);
    }

    #[Test]
    public function it_can_update_a_category()
    {
        $this->postJson('/api/categories', [
            'name' => 'Electronics',
            'parent_id' => 'root'
        ]);
        $id = Redis::keys('category:*')[0];

        $response = $this->putJson("/api/categories/$id", [
            'name' => 'Updated Electronics',
            'parent_id' => 'root'
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_delete_a_category()
    {
        $this->postJson('/api/categories', [
            'name' => 'Electronics',
            'parent_id' => 'root'
        ]);
        $id = Redis::keys('category:*')[0];

        $response = $this->deleteJson("/api/categories/$id");

        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_get_a_category()
    {
        $this->postJson('/api/categories', [
            'name' => 'Electronics',
            'parent_id' => 'root'
        ]);
        $id = Redis::keys('category:*')[0];

        $response = $this->getJson("/api/categories/$id");

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'name', 'parent_id']);
    }

    #[Test]
    public function it_can_get_all_categories()
    {
        $this->postJson('/api/categories', [
            'name' => 'Electronics',
            'parent_id' => 'root'
        ]);
        $this->postJson('/api/categories', [
            'name' => 'Fashion',
            'parent_id' => 'root'
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    #[Test]
    public function it_can_get_the_category_tree()
    {
        $this->postJson('/api/categories', [
            'name' => 'Electronics',
            'parent_id' => 'root'
        ]);
        $this->postJson('/api/categories', [
            'name' => 'Phones',
            'parent_id' => 'root'
        ]);

        $response = $this->getJson('/api/categories/tree');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'name',
                         'parent_id',
                         'children' => [
                             '*' => [
                                 'id',
                                 'name',
                                 'parent_id',
                                 'children'
                             ]
                         ]
                     ]
                 ]);
    }

    #[Test]
    public function it_throws_validation_error_for_duplicate_category_name()
    {
        $this->postJson('/api/categories', [
            'name' => 'Duplicate',
            'parent_id' => 'root'
        ]);

        $response = $this->postJson('/api/categories', [
            'name' => 'Duplicate',
            'parent_id' => 'root'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }
}
