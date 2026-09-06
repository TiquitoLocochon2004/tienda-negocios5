<?php

namespace Tests\Unit;

use App\DTOs\CheckoutDataDTO;
use PHPUnit\Framework\TestCase;

class CheckoutDataDTOTest extends TestCase
{
    public function test_checkout_dto_calculates_total_and_formats_amounts(): void
    {
        $checkout = new CheckoutDataDTO(100.00, 21.00, 50.00, []);

        $this->assertSame(171.0, $checkout->total);
        $this->assertSame([
            'resumen' => [
                'subtotal' => '100.00',
                'impuestos' => '21.00',
                'costo_envio' => '50.00',
                'total' => '171.00',
            ],
            'items' => [],
        ], $checkout->toArray());
    }
}