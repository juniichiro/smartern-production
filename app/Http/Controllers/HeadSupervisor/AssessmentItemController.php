<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Models\AssessmentItem;
use Illuminate\Http\Request;

class AssessmentItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = AssessmentItem::ordered()->get()->groupBy('category');

        return view('head-supervisor.assessment-items.index', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'competency_text' => 'required|string|max:500',
            'order_index' => 'nullable|integer',
        ]);

        AssessmentItem::create($validated);

        return redirect()->route('head-supervisor.assessment-items.index')
            ->with('status', 'Assessment item finalized.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssessmentItem $assessmentItem)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'competency_text' => 'required|string|max:500',
            'order_index' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $assessmentItem->update($validated);

        return redirect()->route('head-supervisor.assessment-items.index')
            ->with('status', 'Assessment item refined.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssessmentItem $assessmentItem)
    {
        $assessmentItem->delete();

        return redirect()->route('head-supervisor.assessment-items.index')
            ->with('status', 'Assessment item withdrawn.');
    }
}
