<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    #[Test]
    public function it_extends_authenticatable(): void
    {
        $user = new User;

        $this::assertInstanceOf(Authenticatable::class, $user);
    }

    #[Test]
    public function it_uses_required_traits(): void
    {
        $traits = class_uses_recursive(User::class);

        $this::assertContains(HasFactory::class, $traits);
        $this::assertContains(Notifiable::class, $traits);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $user = new User;

        $this::assertSame(['name', 'email', 'password'], $user->getFillable());
    }

    #[Test]
    public function it_has_correct_hidden_attributes(): void
    {
        $user = new User;

        $this::assertSame(['password', 'remember_token'], $user->getHidden());
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $user = new User;
        $casts = $user->getCasts();

        $this::assertArrayHasKey('email_verified_at', $casts);
        $this::assertArrayHasKey('password', $casts);
        $this::assertSame('datetime', $casts['email_verified_at']);
        $this::assertSame('hashed', $casts['password']);
    }

    #[Test]
    public function it_can_be_created_via_factory(): void
    {
        $user = User::factory()->make();

        $this::assertInstanceOf(User::class, $user);
        $this::assertNotEmpty($user->name);
        $this::assertNotEmpty($user->email);
        $this::assertNotEmpty($user->password);
    }

    #[Test]
    public function it_hides_password_and_remember_token_in_serialization(): void
    {
        $user = User::factory()->make([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'remember_token' => 'some-token',
        ]);

        $array = $user->toArray();

        $this::assertArrayNotHasKey('password', $array);
        $this::assertArrayNotHasKey('remember_token', $array);
        $this::assertArrayHasKey('name', $array);
        $this::assertArrayHasKey('email', $array);
    }
}
