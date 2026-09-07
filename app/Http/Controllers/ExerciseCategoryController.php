<?php

namespace App\Http\Controllers;

use App\Models\ExerciseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExerciseCategoryController extends Controller
{
    /**
     * Store a newly created exercise category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:exercise_categories,name'],
        ]);

        $category = ExerciseCategory::create($validated);

        return response()->json([
            'value' => $category->id,
            'label' => $category->name,
        ], 201);
    }
}
