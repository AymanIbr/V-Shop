<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category', 'brand', 'images')->latest()->get();
        $categories = Category::select('name', 'id')->get();
        $brands = Brand::select('name', 'id')->get();
        return Inertia::render('Admin/Product/Index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string'],
            'quantity'     => ['required', 'integer', 'min:0'],
            'price'        => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_published' => ['boolean'],
            'category_id'  => ['nullable', 'exists:categories,id'],
            'brand_id'     => ['nullable', 'exists:brands,id'],
            'images'   => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $data['in_stock'] = $data['quantity'] > 0;
        $product = Product::create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images', []) as $image) {
                $path = $image->store('products', 'custom');
                $product->images()->create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }


    public function togglePublished(Product $product)
    {
        $product->update(['is_published' => ! $product->is_published]);

        return back();
    }

    public function toggleStock(Product $product)
    {
        $product->update(['in_stock' => !$product->in_stock]);
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // dd($request->all());
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200',],
            'description' => ['nullable', 'string',],
            'quantity' => ['required', 'integer', 'min:0',],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99',],
            'is_published' => ['sometimes', 'boolean',],
            'category_id' => ['nullable', Rule::exists('categories', 'id'),],
            'brand_id' => ['nullable', Rule::exists('brands', 'id'),],
            'deleted_images' => ['nullable', 'array',],
            'deleted_images.*' => [
                'integer',
                Rule::exists('product_images', 'id'),
            ],
        ]);
        $data['in_stock'] = $data['quantity'] > 0;
        unset($data['deleted_images']);
        $product->update($data);

        if ($request->filled('deleted_images')) {
            $images = $product->images()
                ->whereIn('id', $request->deleted_images)
                ->get();
            foreach ($images as $image) {
                Storage::disk('custom')->delete($image->image);
                $image->delete();
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'custom');
                $product->images()->create([
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('custom')->delete($image->image);
            $image->delete();
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
