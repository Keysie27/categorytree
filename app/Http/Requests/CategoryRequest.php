<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\CategoryService;

class CategoryRequest extends FormRequest
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        parent::__construct();
        $this->categoryService = $categoryService;
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $categoryId = $this->route('id');
        $currentName = $this->categoryService->getCurrentCategoryName($categoryId);

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($currentName) {
                    if ($value !== $currentName && $this->categoryService->categoryNameExists($value)) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
            ],
            'parent_id' => [
                'nullable',
                'string',
                Rule::in($this->categoryService->getCategoryIds())
            ],
        ];
    }
}
