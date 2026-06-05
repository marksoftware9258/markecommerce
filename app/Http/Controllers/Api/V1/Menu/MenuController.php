<?php

namespace App\Http\Controllers\Api\V1\Menu;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Http\Resources\Menu\MenuResource;
use App\Http\Resources\Menu\MenuCollection;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Menu Management
 */
class MenuController extends BaseController
{
    public function __construct(private readonly MenuService $menuService) {}

    public function index(Request $request): JsonResponse
    {
        $menus = Menu::query()
            ->when($request->type, fn ($q, $t) => $q->ofType($t))
            ->when($request->active_only, fn ($q) => $q->active())
            ->with('children', 'roles', 'permissions')
            ->roots()
            ->orderBy('order')
            ->paginate($request->get('per_page', 50));

        return $this->paginatedResponse(MenuCollection::make($menus));
    }

    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = $this->menuService->create($request->validated());

        return $this->createdResponse(new MenuResource($menu->load('children', 'roles')));
    }

    public function show(Menu $menu): JsonResponse
    {
        return $this->successResponse(
            new MenuResource($menu->load('children', 'roles', 'permissions'))
        );
    }

    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $menu = $this->menuService->update($menu, $request->validated());

        return $this->successResponse(new MenuResource($menu->load('children', 'roles')));
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return $this->successResponse(message: 'Menu deleted.');
    }

    /**
     * Get full menu tree
     */
    public function tree(Request $request): JsonResponse
    {
        $tree = $this->menuService->getTree($request->type ?? 'sidebar');

        return $this->successResponse($tree);
    }

    /**
     * Get menus accessible by current user's roles
     */
    public function myMenus(Request $request): JsonResponse
    {
        $menus = $this->menuService->getMenusForUser(auth()->user());

        return $this->successResponse($menus);
    }

    /**
     * Reorder menus
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'items'          => 'required|array',
            'items.*.id'     => 'required|exists:menus,id',
            'items.*.order'  => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:menus,id',
        ]);

        $this->menuService->reorder($request->items);

        return $this->successResponse(message: 'Menus reordered successfully.');
    }
}
