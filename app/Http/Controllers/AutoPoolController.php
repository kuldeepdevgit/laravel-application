<?php

namespace App\Http\Controllers;

use App\Models\UserAutoPoolTree;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutoPoolController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $pool = $request->string('pool')->toString();

        if (! in_array($pool, UserAutoPoolTree::POOLS, true)) {
            $pool = '';
        }

        $placements = UserAutoPoolTree::query()
            ->with(['user', 'tree'])
            ->when($pool !== '', function ($query) use ($pool): void {
                $query->where('pool_type', $pool);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('pool_type', 'like', $like)
                        ->orWhereHas('user', function ($users) use ($like): void {
                            $users->where('name', 'like', $like)
                                ->orWhere('user_name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        })
                        ->orWhereHas('tree', function ($parents) use ($like): void {
                            $parents->where('name', 'like', $like)
                                ->orWhere('user_name', 'like', $like);
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.auto-pool.index', [
            'placements' => $placements,
            'search' => $search,
            'pool' => $pool,
            'total' => UserAutoPoolTree::query()->count(),
        ]);
    }
}
