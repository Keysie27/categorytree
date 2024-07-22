<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // Web Routes

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = $this->categoryService->getCategoryNamesAndIds();
        $prefix = ''; // Definir el prefijo inicial
        return view('categories.create', compact('categories', 'prefix'));
    }

    public function edit($id)
    {
        $category = $this->categoryService->getCategory($id);
        $categories = $this->categoryService->getCategoryNamesAndIds();
        return view('categories.edit', compact('category', 'categories'));
    }

    public function searchView()
    {
        $categories = $this->categoryService->getAllCategories();
        return view('categories.search', compact('categories'));
    }

    // API Routes
   
    public function addCategory(CategoryRequest $request)
    {
        Log::info('addCategory called', ['name' => $request->name, 'parent_id' => $request->parent_id]);
    
        try {
            $id = $this->categoryService->addCategory($request->name, $request->parent_id);
            Log::info('Category added successfully', ['id' => $id]);
            return response()->json(['id' => $id], 201);
        } catch (\Exception $e) {
            Log::error('Error adding category', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unprocessable Content', 'errors' => ['name' => [$e->getMessage()]]], 422);
        }
    }    

    public function updateCategory($id, CategoryRequest $request)
    {
        try {
            $success = $this->categoryService->updateCategory($id, $request->name, $request->parent_id);
            if (!$success) {
                return response()->json(['error' => 'Category not found'], 404);
            }
            return response()->json(['success' => $success]);
        } catch (\Exception $e) {
            return response()->json(['errors' => ['name' => [$e->getMessage()]]], 422); 
        }
    }

    public function deleteCategory($id)
    {
        $success = $this->categoryService->deleteCategory($id);
        if (!$success) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        return response()->json(['success' => $success]);
    }

    public function getCategory($id)
    {
        $category = $this->categoryService->getCategory($id);
        if (empty($category)) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        return response()->json($category);
    }

    public function getAllCategories()
    {
        $categories = $this->categoryService->getAllCategories();
        return response()->json($categories);
    }
    
    public function printTree()
    {
    $tree = $this->categoryService->printTree();
    return response()->json($tree);
    }
}