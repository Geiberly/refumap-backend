<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * GET /api/public/categories
     * Cacheado 30 minutos (cambia raramente).
     */
    public function index()
    {
        $categories = Cache::remember('public_categories', 1800, function () {
            return Category::active()
                ->select(['id', 'name', 'slug', 'icon', 'color'])
                ->orderBy('name')
                ->get()->toArray();
        });

        return response()->json(['data' => $categories]);
    }
}
