<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created_from_the_api(): void
    {
        $category = Categoria::factory()->create();

        $response = $this->postJson('/api/productos', [
            'nombre' => 'Teclado mecánico',
            'precio' => 125.50,
            'stock' => 8,
            'categoria_id' => $category->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nombre', 'Teclado mecánico');

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Teclado mecánico',
            'stock' => 8,
        ]);
    }

    public function test_protected_endpoints_require_a_jwt(): void
    {
        $this->getJson('/api/carrito')->assertUnauthorized();
        $this->postJson('/api/checkout/confirmar', [
            'direccion_envio' => 'Calle 123',
            'metodo_pago' => 'tarjeta',
        ])->assertUnauthorized();
    }

    public function test_user_can_login_and_manage_cart_and_checkout(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $product = Producto::factory()->create([
            'precio' => 100,
            'stock' => 3,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk();

        $token = $login->json('access_token');
        $headers = ['Authorization' => 'Bearer ' . $token];

        $this->getJson('/api/profile', $headers)
            ->assertOk()
            ->assertJsonPath('email', $user->email);

        $this->postJson('/api/carrito', [
            'producto_id' => $product->id,
            'cantidad' => 2,
        ], $headers)->assertCreated();

        $this->deleteJson('/api/carrito/' . $product->id, $headers)
            ->assertOk();

        $this->postJson('/api/carrito', [
            'producto_id' => $product->id,
            'cantidad' => 2,
        ], $headers)->assertCreated();

        $this->mock(PaymentGateway::class, function (MockInterface $mock): void {
            $mock->shouldReceive('charge')
                ->once()
                ->with(292.0, 'tarjeta')
                ->andReturn('test-payment-id');
        });

        $this->getJson('/api/checkout/resumen', $headers)
            ->assertOk()
            ->assertJsonPath('data.resumen.subtotal', '200.00')
            ->assertJsonPath('data.resumen.total', '292.00');

        $this->postJson('/api/checkout/confirmar', [
            'direccion_envio' => 'Calle 123',
            'metodo_pago' => 'tarjeta',
        ], $headers)->assertOk()
            ->assertJsonPath('data.orden.total_pagado', '292.00');

        $this->assertDatabaseHas('productos', [
            'id' => $product->id,
            'stock' => 1,
        ]);
        $this->assertDatabaseMissing('carrito_items', ['user_id' => $user->id]);
    }

    public function test_cart_rejects_quantity_above_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Producto::factory()->create(['stock' => 1]);
        $token = JWTAuth::fromUser($user);

        $this->withToken($token)
            ->postJson('/api/carrito', [
                'producto_id' => $product->id,
                'cantidad' => 2,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrecta',
        ])->assertUnauthorized();
    }
}