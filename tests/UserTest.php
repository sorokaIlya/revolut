<?php

namespace App\Tests;

use App\DTO\CreateUserDto;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserTest extends TestCase
{
    public function testRegistration()
    {
        $dto = new CreateUserDto();
        $dto->email = 'test@email.com';
        $dto->password = 'secure-pass';
        /** @var UserPasswordEncoderInterface|MockObject $encoderMock */
        $encoderMock = $this->createMock(UserPasswordEncoderInterface::class);
        $encoderMock->method('encodePassword')->willReturn('testPassword');
        $user = User::createUserFromRegistration($dto, $encoderMock);
        $this->assertEquals('test@email.com', $user->getEmail());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }
}
