<?php

namespace App\Http\Controllers;

use App\Models\PrivilegeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrivilegeGroupController extends Controller
{
    public function index()
    {
        return response()->json(
            PrivilegeGroup::with(['privileges' => function ($q) {
                $q->orderBy('sort_order');
            }])->latest()->get()
        );
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
