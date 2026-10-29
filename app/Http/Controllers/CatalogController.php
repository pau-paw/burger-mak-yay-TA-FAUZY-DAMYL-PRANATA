<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'sort' => ['nullable', 'string', 'in:name_asc,name_desc,latest'],
        ]);

        $sort = $filters['sort'] ?? null;

        $products = Product::with('category')->where('is_active', true)
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where('name', 'like', '%' . $q . '%'))
            ->when($filters['category'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('name', 'asc'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderBy('name', 'desc'))
            ->when(!$sort || $sort === 'latest', fn ($query) => $query->latest())
            ->paginate(24)
            ->withQueryString()
            ->fragment('menu');

        return view('home.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function legacy(Request $request)
    {
        return redirect()->to(route('home', $request->only(['q', 'category', 'page'])).'#menu');
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $addons = Addon::active()->orderBy('price')->get();

        return view('catalog.show', [
            'product' => $product->load('category'),
            'addons' => $addons,
        ]);
    }
}
