<?php

namespace App\Http\Controllers\Desk;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserModuleGrant;
use App\Models\UserOperationDenial;
use App\Services\AuditLogger;
use App\Services\DeskAccess;
use Illuminate\Http\Request;

class DeskAccessController extends Controller
{
    public function index()
    {
        $users = User::query()->with(['role', 'moduleGrants', 'operationDenials'])->orderBy('name')->get();

        return view('desk.access', [
            'users' => $users,
            'modules' => DeskAccess::MODULES,
            'operations' => DeskAccess::OPERATIONS,
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($user->isGuest()) {
            return redirect()->route('desk.access')->with('error', 'A guest account cannot be granted hotel desk access.');
        }

        $modules = array_values(array_intersect(array_keys($request->input('modules', [])), array_keys(DeskAccess::MODULES)));
        $denials = array_values(array_intersect((array) $request->input('denials', []), array_keys(DeskAccess::OPERATIONS)));

        UserModuleGrant::query()->where('user_id', $user->id)->delete();
        foreach ($modules as $module) {
            UserModuleGrant::query()->create(['user_id' => $user->id, 'module' => $module]);
        }

        UserOperationDenial::query()->where('user_id', $user->id)->delete();
        foreach ($denials as $operation) {
            UserOperationDenial::query()->create(['user_id' => $user->id, 'operation' => $operation]);
        }

        $moduleNames = array_map(fn (string $module) => DeskAccess::MODULES[$module], $modules);
        $denialNames = array_map(fn (string $operation) => DeskAccess::OPERATIONS[$operation], $denials);

        app(AuditLogger::class)->write(
            'module_grant_changed',
            User::class,
            $user->id,
            'Desk access for '.$user->name.' was updated.',
            [
                'name' => $user->name,
                'modules' => $moduleNames,
                'denials' => $denialNames,
            ]
        );

        return redirect()->route('desk.access')->with('success', 'Desk access saved for '.$user->name.'.');
    }
}
