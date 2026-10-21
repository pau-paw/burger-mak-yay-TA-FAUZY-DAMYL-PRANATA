<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'archived' => ['nullable', 'boolean']]);

        return view('admin.products.index', ['products' => Product::with('category')
            ->when($request->boolean('archived'), fn ($query) => $query->onlyTrashed())
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where('name', 'like', '%'.$q.'%'))
            ->latest()->paginate(12)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product(['is_active' => true, 'stock' => 0]), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->safe()->except(['image', 'revision']);
        $path = $request->hasFile('image') ? $request->file('image')->store('products', 'public') : null;
        try {
            Product::create([...$data, 'image_path' => $path]);
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        return redirect()->route('admin.products.index')->with('success', 'Menu ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->safe()->except(['image', 'revision']);
        $revision = (int) $request->validated('revision');
        $path = $request->hasFile('image') ? $request->file('image')->store('products', 'public') : null;
        try {
            $oldPath = DB::transaction(function () use ($product, $data, $path, $revision) {
                $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
                if ($locked->revision !== $revision) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['stock' => 'Menu atau stok sudah berubah. Muat ulang halaman edit untuk mengambil data terbaru sebelum menyimpan.']);
                }
                $old = $locked->image_path;
                $locked->revision++;
                $locked->update($path ? [...$data, 'image_path' => $path] : $data);

                return $old;
            });
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }
        if ($path && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.products.index')->with('success', 'Menu diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Retain historical relations and snapshots; hide the menu from sale.
        $product->delete();

        return back()->with('success', 'Menu diarsipkan. Riwayat pesanan tetap tersimpan.');
    }

    public function restore(Product $product)
    {
        abort_unless($product->trashed(), 404);
        $product->is_active = false;
        $product->revision++;
        $product->restore();

        return redirect()->route('admin.products.edit', $product)->with('success', 'Menu dipulihkan sebagai nonaktif. Periksa stok dan aktifkan kembali bila siap.');
    }
}
