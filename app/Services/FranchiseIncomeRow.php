<?php

namespace App\Services;

use App\Models\Income;

class FranchiseIncomeRow
{
    public function __construct(
        public int $id,
        public int $userId,
        public ?int $orderId,
        public ?string $createdAt,
        public ?string $orderNumber,
        public ?string $customerName,
        public ?string $customerUserName,
        public ?string $sponsorName,
        public ?string $sponsorUserName,
        public string $orderBv,
        public string $percent,
        public string $amount,
        public float $amountValue,
    ) {}

    public static function fromIncome(Income $income): self
    {
        $customerName = $income->order?->name ?: $income->fromUser?->name;

        return new self(
            id: (int) $income->getKey(),
            userId: (int) $income->user_id,
            orderId: $income->order_id === null ? null : (int) $income->order_id,
            createdAt: $income->created_at?->format('M j, Y g:i A'),
            orderNumber: $income->order?->number,
            customerName: is_string($customerName) && $customerName !== '' ? $customerName : null,
            customerUserName: filled($income->fromUser?->user_name) ? $income->fromUser->user_name : null,
            sponsorName: filled($income->user?->name) ? $income->user->name : null,
            sponsorUserName: filled($income->user?->user_name) ? $income->user->user_name : null,
            orderBv: $income->formattedOrderBv(),
            percent: $income->formattedPercent(),
            amount: $income->formattedAmount(),
            amountValue: round((float) $income->amount, 2),
        );
    }
}
