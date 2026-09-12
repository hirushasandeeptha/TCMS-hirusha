<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Models\SystemModule;

/**
 * Controller: ModuleController
 *
 * Manages the SaaS Marketplace — browse available modules,
 * subscribe (buy), and unsubscribe (remove) modules per teacher.
 */
final class ModuleController extends Controller
{
    /**
     * List all available modules with the user's subscription status.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $modules = SystemModule::active()
            ->with(['users' => function ($query) use ($user) {
                $query->where('user_modules.user_id', $user->id);
            }])
            ->get()
            ->map(fn (SystemModule $module) => [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'description' => $module->description,
                'price' => $module->price,
                'is_subscribed' => $module->users->contains($user->id),
                'subscription' => $module->users
                    ->firstWhere('id', $user->id)?->pivot,
            ]);

        return response()->json(['modules' => $modules]);
    }

    /**
     * Subscribe (buy) a module for the authenticated teacher.
     */
    public function subscribe(Request $request, SystemModule $module): JsonResponse
    {
        $user = $request->user();

        if ($user->hasModuleAccess($module->slug)) {
            return response()->json([
                'message' => "You already have access to the '{$module->name}' module.",
            ], 409);
        }

        $user->subscribedModules()->attach($module->id, [
            'subscribed_at' => now(),
            'status' => 'active',
        ]);

        return response()->json([
            'message' => "Successfully subscribed to the '{$module->name}' module.",
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
            ],
        ], 201);
    }

    /**
     * Unsubscribe (remove) a module from the authenticated teacher.
     */
    public function unsubscribe(Request $request, SystemModule $module): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasModuleAccess($module->slug)) {
            return response()->json([
                'message' => "You do not have an active subscription to '{$module->name}'.",
            ], 404);
        }

        $user->subscribedModules()->detach($module->id);

        return response()->json([
            'message' => "Successfully unsubscribed from the '{$module->name}' module.",
        ]);
    }
}
