<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    public function index()
    {
        $categories = TicketCategory::all();
        return view('tickets.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ticket_categories',
            'suggested_response' => 'nullable|string',
        ]);

        TicketCategory::create($request->all());

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, TicketCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ticket_categories,name,' . $category->id,
            'suggested_response' => 'nullable|string',
        ]);

        $category->update($request->all());

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(TicketCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
