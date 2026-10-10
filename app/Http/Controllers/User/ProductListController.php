<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductListController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with([
                'category',
                'brand',
                'images',
            ])
            ->where('is_published', true);

        // Brand filtering
        if ($request->filled('brands')) {
            $brandIds = (array) $request->input('brands');

            $query->whereIn('brand_id', $brandIds);
        }

        // Category filtering
        if ($request->filled('categories')) {
            $categoryIds = (array) $request->input('categories');

            $query->whereIn('category_id', $categoryIds);
        }

        // Price filtering
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'newest');

        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;

            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        $products = $query
            ->paginate(8)
            ->withQueryString();

        return Inertia::render('User/ProductList', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'filters'    => $request->only(['brands', 'categories', 'min_price', 'max_price', 'sort_by']),
        ]);
    }
}
