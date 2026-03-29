<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssessmentCategory;

class AssessmentCategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $type = $request->input('type');
        $status = $request->input('is_active');

        $query = AssessmentCategory::query();

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status);
        }

        $categories = $query->latest()->paginate(10)->withQueryString();
        
        // Get unique types for filter dropdown
        $types = AssessmentCategory::distinct()->pluck('type');

        return view('assessment_category.index', compact('categories', 'types'));
    }

    public function create()
    {
        return view('assessment_category.form', [
            'assessmentCategory' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        AssessmentCategory::create($data);

        return redirect()->route('assessment_category.index')
            ->with('success', 'Assessment category created successfully');
    }

    public function edit(AssessmentCategory $assessmentCategory)
    {
        return view('assessment_category.form', compact('assessmentCategory'));
    }

    public function update(Request $request, AssessmentCategory $assessmentCategory)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $assessmentCategory->update($data);

        return redirect()->route('assessment_category.index')
            ->with('success', 'Assessment category updated successfully');
    }

    public function destroy(AssessmentCategory $assessmentCategory)
    {
        $assessmentCategory->delete();
        return redirect()->route('assessment_category.index')
            ->with('success', 'Assessment category deleted successfully');
    }
}
