<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AddonController extends Controller
{
    public function index()
    {
        $addons = Addon::orderByDesc('is_active')->orderBy('name')->paginate(15);

        return view('admin.addons.index', compact('addons'));
    }

    public function create()
    {
        return view('admin.addons.form', [
            'addon' => new Addon(['is_active' => true, 'price' => 0]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:addons,name'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        Addon::create($data);

        return redirect()->route('admin.addons.index')->with('success', 'Topping/Add-on baru berhasil ditambahkan.');
    }

    public function edit(Addon $addon)
    {
        return view('admin.addons.form', [
            'addon' => $addon,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Addon $addon)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('addons')->ignore($addon->id)],
            'price' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $addon->update($data);

        return redirect()->route('admin.addons.index')->with('success', 'Data topping/add-on berhasil diperbarui.');
    }

    public function destroy(Addon $addon)
    {
        $addon->delete();

        return redirect()->route('admin.addons.index')->with('success', 'Topping/Add-on berhasil dihapus.');
    }
}
