<?php

namespace App\Domain\Contracts;

interface ProductPricingContract
{
    /**
     * @param array $input validated fields
     * @return array {
     *   unit_price: float,
     *   qty: int,
     *   total: float,
     *   breakdown: array,
     *   spec_snapshot: array
     * }
     */
    public function quote(array $input): array;
}
