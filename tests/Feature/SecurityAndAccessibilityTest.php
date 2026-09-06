<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class SecurityAndAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_exposes_basic_accessibility_structure(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('<html lang="', false)
            ->assertSee('<meta charset="utf-8">', false)
            ->assertSee('<meta name="viewport"', false)
            ->assertSee('<h1', false);
    }

    public function test_invalid_jwt_is_rejected_by_protected_api_routes(): void
    {
        $this->withHeader('Authorization', 'Bearer invalid.token.value')
            ->getJson('/api/profile')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'Token Invalid');
    }

    public function test_product_validation_rejects_maliciously_invalid_input(): void
    {
        $category = Categoria::factory()->create();
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this->withToken($token)
            ->postJson('/api/productos', [
                'nombre' => '<script>alert(1)</script>',
                'precio' => -10,
                'stock' => -1,
                'categoria_id' => $category->id,
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }
}
