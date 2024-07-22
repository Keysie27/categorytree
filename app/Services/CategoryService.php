<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    protected $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
        $this->initializeRootCategory();
    }

    protected function initializeRootCategory()
    {
        if (!$this->redis->exists("category:root")) {
            $rootCategory = [
                'id' => 'root',
                'name' => 'Root',
                'parent_id' => null,
            ];
            $this->redis->hset("category:root", 'id', $rootCategory['id'], 'name', $rootCategory['name'], 'parent_id', $rootCategory['parent_id']);
        }
    }

    public function addCategory(string $name, ?string $parentId = null): string
    {
        Log::info('addCategory service called', ['name' => $name, 'parent_id' => $parentId]);

        if ($this->categoryNameExists($name)) {
            throw new \Exception("The name has already been taken.");
        }

        $id = Str::uuid()->toString();
        $parentId = $parentId ?: 'root';

        $category = [
            'id' => $id,
            'name' => $name,
            'parent_id' => $parentId,
        ];

        Log::info('Storing category in Redis', ['category' => $category]);

        $this->redis->multi()
            ->hset("category:$id", 'id', $category['id'], 'name', $category['name'], 'parent_id', $category['parent_id'])
            ->sadd("category:$parentId:children", $id)
            ->exec();

        Log::info('Category added successfully', ['id' => $id]);

        return $id;
    }


    public function updateCategory(string $id, string $name, ?string $parentId = null): bool
    {
        $category = $this->redis->hgetall("category:$id");
        if (!$category) {
            throw new \Exception("Category not found");
        }

        if ($name !== $category['name'] && $this->categoryNameExists($name)) {
            throw new \Exception("The name has already been taken.");
        }

        $oldParentId = $category['parent_id'];
        $parentId = $parentId ?? $oldParentId;

        $this->redis->multi()
            ->hset("category:$id", 'name', $name, 'parent_id', $parentId)
            ->srem("category:$oldParentId:children", $id)
            ->sadd("category:$parentId:children", $id)
            ->exec();

        return true;
    }

    public function deleteCategory(string $id): bool
{
    $category = $this->redis->hgetall("category:$id");
    if (!$category) {
        return false;
    }

    $parentId = $category['parent_id'];

    // Eliminar recursivamente las subcategorías
    $this->deleteChildrenRecursively($id);

    // Eliminar la categoría y la referencia de su padre
    $this->redis->multi()
        ->srem("category:$parentId:children", $id)
        ->del("category:$id")
        ->exec();

    return true;
}

protected function deleteChildrenRecursively(string $id)
{
    $children = $this->redis->smembers("category:$id:children");
    foreach ($children as $childId) {
        // Eliminar recursivamente los hijos del hijo
        $this->deleteChildrenRecursively($childId);
        // Eliminar el hijo
        $this->redis->del("category:$childId");
    }
    // Eliminar el set de hijos
    $this->redis->del("category:$id:children");
}


    public function getCategory(string $id): array
    {
        if (is_null($id)) {
            throw new \Exception("Category ID is required");
        }

        return $this->redis->hgetall("category:$id");
    }

    public function getAllCategories(): array
    {
        $keys = $this->redis->keys("category:*");
        $categories = [];
        foreach ($keys as $key) {
            if (strpos($key, ':children') === false) {
                $category = $this->redis->hgetall($key);
                if (!empty($category)) {
                    $categories[] = $category;
                }
            }
        }
        usort($categories, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        return $categories;
    }

    public function printTree(): array
    {
        $rootCategories = $this->redis->smembers('category:root:children');
        $tree = [];

        foreach ($rootCategories as $rootId) {
            $tree[] = $this->buildTree($rootId);
        }

        Log::info('Built category tree', ['tree' => $tree]);

        return $tree;
    }

    protected function buildTree(string $id): array
    {
        $category = $this->redis->hgetall("category:$id");
        $childrenIds = $this->redis->smembers("category:$id:children");
        $children = [];

        foreach ($childrenIds as $childId) {
            $children[] = $this->buildTree($childId);
        }

        $category['children'] = $children;
        return $category;
    }

    // Support methods

    public function categoryNameExists($name): bool
    {
        $categories = $this->getCategoryNamesAndIds();
        foreach ($categories as $category) {
            if ($category['name'] === $name) {
                return true;
            }
        }
        return false;
    }

    public function getCurrentCategoryName($categoryId)
    {
        if (is_null($categoryId)) {
            return null;
        }

        $category = $this->getCategory($categoryId);
        return $category['name'] ?? null;
    }

    public function getCategoryIds()
    {
        $categories = $this->getCategoryNamesAndIds();
        return array_column($categories, 'id');
    }

    public function getCategoryNamesAndIds(): array
    {
        $categoryKeys = $this->redis->keys("category:*");
        $categories = [];
        foreach ($categoryKeys as $key) {
            if (strpos($key, ':children') === false) {
                $category = $this->redis->hgetall($key);
                if (isset($category['id']) && isset($category['name'])) {
                    $categories[] = [
                        'id' => $category['id'],
                        'name' => $category['name']
                    ];
                } else {
                    Log::warning('Category missing id or name', ['key' => $key, 'category' => $category]);
                }
            }
        }
        return $categories;
    }

}
