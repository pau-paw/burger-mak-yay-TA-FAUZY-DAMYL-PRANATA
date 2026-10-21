<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', ['categories' => Category::withCount('products')->orderBy('name')->paginate(15)]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Kategori ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        DB::transaction(function () use ($category) {
            $locked = Category::whereKey($category->id)->lockForUpdate()->firstOrFail();
            if ($locked->products()->withTrashed()->exists()) {
                throw ValidationException::withMessages(['category' => 'Kategori masih digunakan menu (termasuk menu arsip). Pindahkan kategorinya dahulu.']);
            }
            $locked->delete();
        });

        return back()->with('success', 'Kategori dihapus.');
    }
}
