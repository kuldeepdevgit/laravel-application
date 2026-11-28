<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAutoPoolTree;
use App\Support\Impersonation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutoPoolController extends Controller
{
    public function show(Request $request): View
    {
        $pool = $request->string('pool')->toString();

        if ($pool === '') {
            $pool = UserAutoPoolTree::POOL_SILVER;
        }

        abort_unless(in_array($pool, UserAutoPoolTree::POOLS, true), 404);

        $user = Impersonation::account($request);
        abort_unless($user instanceof User, 403);

        [$heading, $intro] = match ($pool) {
            UserAutoPoolTree::POOL_GOLD => ['2x Matrix Income — Gold', 'Your gold seat in the 2x matrix, and the two positions under you.'],
            UserAutoPoolTree::POOL_DIAMOND => ['2x Matrix Income — Diamond', 'Your diamond seat in the 2x matrix, and the two positions under you.'],
            default => ['2x Matrix Income — Silver', 'Your silver seat in the 2x matrix, and the two positions under you.'],
        };

        return view('dashboard.autopool', [
            'heading' => $heading,
            'intro' => $intro,
            'user' => $user,
            'pool' => $pool,
            'autoPoolSeat' => $user->autoPoolTrees()->with('tree')->where('pool_type', $pool)->first(),
            'autoPoolChildren' => UserAutoPoolTree::query()
                ->with('user')
                ->where('tree_id', $user->getKey())
                ->where('pool_type', $pool)
                ->orderBy('position')
                ->orderBy('id')
                ->get(),
            'matrix' => UserAutoPoolTree::matrixUnder($user, $pool),
        ]);
    }

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
