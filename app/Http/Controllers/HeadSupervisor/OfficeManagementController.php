<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGeofenceRequest;
use App\Http\Requests\HeadSupervisor\StoreOfficeRequest;
use App\Http\Requests\HeadSupervisor\UpdateOfficeRequest;
use App\Http\Requests\HeadSupervisor\UpdateOfficeRulesRequest;
use App\Models\AssessmentItem;
use App\Models\Office;
use App\Models\OfficeRule;
use Illuminate\Support\Facades\DB;

class OfficeManagementController extends Controller
{
    public function index()
    {
        return redirect()->route('head-supervisor.onboarding.index', ['#offices']);
    }

    public function editRules(Office $office)
    {
        $rules = OfficeRule::where('office_id', $office->id)->get();
        $assessmentItems = AssessmentItem::active()->ordered()->get()->groupBy('category');

        return view('head-supervisor.offices.edit-rules', compact('office', 'rules', 'assessmentItems'));
    }

    public function updateRules(UpdateOfficeRulesRequest $request, Office $office)
    {
        DB::transaction(function () use ($request, $office) {
            // Update Office Limit
            $office->update(['intern_limit' => $request->intern_limit]);

            // Clear existing rules
            OfficeRule::where('office_id', $office->id)->delete();

            // Insert new rules
            if ($request->has('rules')) {
                foreach ($request->rules as $ruleData) {
                    // Only save rules that are actually required (priority > 1)
                    if ($ruleData['priority'] > 1) {
                        OfficeRule::create([
                            'office_id' => $office->id,
                            'require_rule' => $ruleData['require_rule'],
                            'rule_value' => $ruleData['rule_value'],
                            'priority' => $ruleData['priority'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('head-supervisor.onboarding.index', ['#offices'])
            ->with('success', "Rules for {$office->name} refined.");
    }

    public function store(StoreOfficeRequest $request)
    {
        DB::transaction(function () use ($request) {
            $office = Office::create([
                'name' => $request->name,
                'location_data' => null,
            ]);

            if ($request->has('rules')) {
                foreach ($request->rules as $ruleData) {
                    OfficeRule::create([
                        'office_id' => $office->id,
                        'require_rule' => $ruleData['require_rule'],
                        'rule_value' => $ruleData['rule_value'],
                        'priority' => $ruleData['priority'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Office created successfully.');
    }

    public function update(UpdateOfficeRequest $request, Office $office)
    {
        $office->update(['name' => $request->name]);

        return back()->with('success', 'Office name refined.');
    }

    public function updateGeofence(UpdateGeofenceRequest $request)
    {
        if ($request->filled('office_id')) {
            $office = Office::findOrFail($request->office_id);
            $office->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius_meters' => $request->radius_meters,
            ]);

            return back()->with('success', "Geofence refined for {$office->name}.");
        }

        Office::query()->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meters' => $request->radius_meters,
        ]);

        return back()->with('success', 'Global geofence refined for all offices.');
    }

    public function destroy(Office $office)
    {
        // Check if office has interns or supervisors
        if ($office->internDetails()->exists() || $office->supervisorDetails()->exists()) {
            return back()->with('error', "Withdrawal restricted: {$office->name} has active assignments.");
        }

        DB::transaction(function () use ($office) {
            // Delete associated rules first
            OfficeRule::where('office_id', $office->id)->delete();
            $office->delete();
        });

        return back()->with('success', "Office {$office->name} withdrawn.");
    }
}
