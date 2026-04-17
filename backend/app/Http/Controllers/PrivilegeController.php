<?php

namespace App\Http\Controllers;

use App\Models\Privilege;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrivilegeController extends Controller
{
    public function index(Request $request)
    {
        $query = Privilege::with(['group', 'parent', 'children']);

        if ($request->has('group_id')) {
            $query->where('privilege_group_id', $request->group_id);
        }

        return response()->json($query->orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'privilege_group_id' => 'nullable|exists:privilege_groups,id',
            'parent_id' => 'nullable|exists:privileges,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:privileges,slug',
            'frontend_route' => 'nullable|string|max:255',
            'api_route' => 'nullable|string|max:255',
            'method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'menu_type' => 'in:menu,submenu,none',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'integer',
            'show_in_menu' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'privilege_group_id', 'parent_id', 'name', 'frontend_route', 'api_route', 'method',
            'menu_type', 'icon', 'sort_order', 'show_in_menu', 'description', 'is_active',
        ]);
        $data['slug'] = $request->slug ?? Str::slug($request->name);

        $privilege = Privilege::create($data);

        return response()->json($privilege->load(['group', 'parent']), 201);
    }

    public function show(Privilege $privilege)
    {
        return response()->json($privilege->load(['group', 'parent', 'children', 'roles']));
    }

    public function update(Request $request, Privilege $privilege)
    {
        $request->validate([
            'privilege_group_id' => 'nullable|exists:privilege_groups,id',
            'parent_id' => 'nullable|exists:privileges,id',
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:privileges,slug,' . $privilege->id,
            'frontend_route' => 'nullable|string|max:255',
            'api_route' => 'nullable|string|max:255',
            'method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'menu_type' => 'in:menu,submenu,none',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'integer',
            'show_in_menu' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $privilege->update($request->only([
            'privilege_group_id', 'parent_id', 'name', 'slug', 'frontend_route', 'api_route', 'method',
            'menu_type', 'icon', 'sort_order', 'show_in_menu', 'description', 'is_active',
        ]));

        return response()->json($privilege->load(['group', 'parent', 'children']));
    }
}
