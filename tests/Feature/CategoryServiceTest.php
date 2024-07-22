<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryServiceTest extends TestCase
{
    protected $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService();
        Redis::flushall(); // Limpiar Redis antes de cada prueba
    }

    public function test_it_can_create_a_category()
    {
        $name = 'Electronics';
        $parentId = 'root';

        $id = $this->categoryService->addCategory($name, $parentId);

        $this->assertNotNull($id);
        $this->assertEquals(Redis::hget("category:$id", 'name'), $name);
        $this->assertEquals(Redis::hget("category:$id", 'parent_id'), $parentId);
    }

    public function test_it_can_update_a_category()
    {
        $name = 'Electronics';
        $parentId = 'root';
        $id = $this->categoryService->addCategory($name, $parentId);

        $newName = 'Updated Electronics';
        $this->categoryService->updateCategory($id, $newName, $parentId);

        $this->assertEquals(Redis::hget("category:$id", 'name'), $newName);
    }

    public function test_it_can_delete_a_category()
    {
        $name = 'Electronics';
        $parentId = 'root';
        $id = $this->categoryService->addCategory($name, $parentId);

        // Agregar una subcategoría para verificar la eliminación recursiva
        $childId = $this->categoryService->addCategory('Phones', $id);

        $this->categoryService->deleteCategory($id);

        // Verificar que la categoría y sus hijos hayan sido eliminados
        $this->assertFalse(Redis::exists("category:$id"));
        $this->assertFalse(Redis::exists("category:$childId"));
        $this->assertFalse(Redis::exists("category:$id:children"));
    }

    public function test_it_can_get_a_category()
    {
        $name = 'Electronics';
        $parentId = 'root';
        $id = $this->categoryService->addCategory($name, $parentId);

        $category = $this->categoryService->getCategory($id);

        $this->assertEquals($category['id'], $id);
        $this->assertEquals($category['name'], $name);
        $this->assertEquals($category['parent_id'], $parentId);
    }

    public function test_it_can_get_all_categories()
    {
        $this->categoryService->addCategory('Electronics', 'root');
        $this->categoryService->addCategory('Fashion', 'root');

        $categories = $this->categoryService->getAllCategories();

        $this->assertCount(2, $categories);
    }

    public function test_it_can_get_the_category_tree()
    {
        $rootId = 'root';
        $this->categoryService->addCategory('Electronics', $rootId);
        $this->categoryService->addCategory('Phones', $rootId);

        $tree = $this->categoryService->printTree();

        $this->assertNotEmpty($tree);
    }

    public function test_it_throws_validation_error_for_duplicate_category_name()
    {
        $this->categoryService->addCategory('Duplicate', 'root');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The name has already been taken.');

        $this->categoryService->addCategory('Duplicate', 'root');
    }
}
