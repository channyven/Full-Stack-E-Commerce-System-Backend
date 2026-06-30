<?php

namespace App\DTOs;

final class CreateOrderDto
{
    public function __construct(
        public readonly int $userId,
        public readonly array $items,
        public readonly array $shippingAddress,
        public readonly ?array $billingAddress = null,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $couponCode = null,
        public readonly float $taxAmount = 0.0,
        public readonly float $shippingAmount = 0.0,
        public readonly ?string $notes = null,
    ) {}
}
