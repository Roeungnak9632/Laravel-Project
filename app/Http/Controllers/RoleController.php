<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Role.View')->only(['index']);
        $this->middleware('permission:Role.Create')->only(['store']);
        $this->middleware('permission:Role.Update')->only(['update']);
        $this->middleware('permission:Role.Remove')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->filled('text_search')) {
            $search = $request->text_search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $list = $query->orderBy('id', 'desc')->get();

        return response()->json([
            "success" => true,
            "list" => $list
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|unique:roles,code',
            'description' => 'required|string',
            'status'      => 'required|boolean',
        ]);

        $role = Role::create($data);

        return response()->json([
            "success" => true,
            "message" => "Role created successfully",
            "data" => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:255',

            'code'        => ['required', 'string', Rule::unique('roles')->ignore($role->id)],
            'description' => 'required|string',
            'status'      => 'required|boolean',
        ]);

        $role->update($data);

        return response()->json([
            "success" => true,
            "message" => "Role updated successfully",
            "data" => $role
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            "success" => true,
            "message" => "Role deleted successfully"
        ]);
    }
}
