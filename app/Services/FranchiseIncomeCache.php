<?php

namespace App\Services;

use App\Models\Income;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FranchiseIncomeCache
{
    private const string ALL = 'franchise-income.all';

    private const string INDEX = 'franchise-income.keys';

    /**
     * @return Collection<int, FranchiseIncomeRow>
     */
    public function all(): Collection
    {
        return $this->remember(self::ALL, fn (): Collection => $this->snapshots());
    }

    /**
     * @return Collection<int, FranchiseIncomeRow>
     */
    public function forUser(int $userId): Collection
    {
        return $this->remember($this->userKey($userId), fn (): Collection => $this->snapshots($userId));
    }

    /**
     * Replace the Redis lists after a franchise credit is stored.
     */
    public function refresh(): void
    {
        $rows = $this->snapshots();
        $this->put(self::ALL, $rows);

        foreach ($rows->groupBy(fn (FranchiseIncomeRow $row): int => $row->userId) as $userId => $userRows) {
            $this->put($this->userKey((int) $userId), $userRows->values());
        }
    }

    public function forget(): void
    {
        $keys = $this->store()->get(self::INDEX, []);

        if (! is_array($keys)) {
            $keys = [];
        }

        foreach ($keys as $key) {
            $this->store()->forget($key);
        }

        $this->store()->forget(self::INDEX);
    }

    /**
     * @param  Collection<int, FranchiseIncomeRow>  $rows
     * @return Collection<int, FranchiseIncomeRow>
     */
    public function matching(Collection $rows, string $search): Collection
    {
        $search = mb_strtolower(trim($search));

        if ($search === '') {
            return $rows;
        }

        return $rows->filter(function (FranchiseIncomeRow $row) use ($search): bool {
            foreach ([
                $row->orderNumber,
                $row->customerName,
                $row->customerUserName,
                $row->sponsorName,
                $row->sponsorUserName,
            ] as $value) {
                if (is_string($value) && str_contains(mb_strtolower($value), $search)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    /**
     * @param  Collection<int, FranchiseIncomeRow>  $rows
     * @return LengthAwarePaginator<int, FranchiseIncomeRow>
     */
    public function paginate(Collection $rows, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $page = max(1, (int) $request->integer('page', 1));

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    /**
     * @return Collection<int, FranchiseIncomeRow>
     */
    private function snapshots(?int $userId = null): Collection
    {
        return Income::query()
            ->where('type', Income::TYPE_FRANCHISE)
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->with(['user', 'order', 'fromUser'])
            ->latest('id')
            ->get()
            ->map(fn (Income $income): FranchiseIncomeRow => FranchiseIncomeRow::fromIncome($income))
            ->values();
    }

    private function userKey(int $userId): string
    {
        return 'franchise-income.user.'.$userId;
    }

    /**
     * @param  callable(): Collection<int, FranchiseIncomeRow>  $callback
     * @return Collection<int, FranchiseIncomeRow>
     */
    private function remember(string $key, callable $callback): Collection
    {
        $cached = $this->store()->get($key);

        if ($cached instanceof Collection) {
            return $cached;
        }

        $rows = $callback();
        $this->put($key, $rows);

        return $rows;
    }

    private function put(string $key, mixed $value): void
    {
        $this->store()->forever($key, $value);

        $keys = $this->store()->get(self::INDEX, []);
        $keys = is_array($keys) ? $keys : [];
        $keys[] = $key;
        $this->store()->forever(self::INDEX, array_values(array_unique($keys)));
    }

    private function store(): Repository
    {
        return Cache::store((string) config('cache.franchise_income_store', 'redis'));
    }
}
