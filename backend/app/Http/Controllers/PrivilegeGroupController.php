<?php

namespace App\Http\Controllers;

use App\Models\PrivilegeGroup;
use App\Models\Privilege;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrivilegeGroupController extends Controller
{
    public function index()
    {
        // Get all privilege groups with their privileges
        $groups = PrivilegeGroup::with(['privileges' => function ($q) {
            $q->orderBy('sort_order');
        }])->latest()->get()->toArray();

        // Get privileges with null group_id
        $ungroupedPrivileges = Privilege::whereNull('privilege_group_id')
            ->orderBy('sort_order')
            ->get();

        // If there are ungrouped privileges, add them as a special group
        if ($ungroupedPrivileges->count() > 0) {
            $ungroupedGroup = [
                'id' => 0,
                'name' => 'Ungrouped',
                'slug' => 'ungrouped',
                'description' => 'Privileges without a group',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'privileges' => $ungroupedPrivileges->toArray(),
            ];
            $groups[] = $ungroupedGroup;
        }

        return response()->json($groups);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:privilege_groups,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['name', 'description', 'is_active']);
        $data['slug'] = $request->slug ?? Str::slug($request->name);

        $group = PrivilegeGroup::create($data);

        return response()->json($group, 201);
    }

    public function show(PrivilegeGroup $privilegeGroup)
    {
        return response()->json($privilegeGroup->load(['privileges' => function ($q) {
            $q->orderBy('sort_order');
        }]));
    }

    public function update(Request $request, PrivilegeGroup $privilegeGroup)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:privilege_groups,slug,' . $privilegeGroup->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $privilegeGroup->update($request->only(['name', 'slug', 'description', 'is_active']));

        return response()->json($privilegeGroup->load('privileges'));
    }
}
