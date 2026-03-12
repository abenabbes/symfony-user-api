<?php

declare(strict_types=1);

namespace tests\Service;

use App\Entity\User;
use App\Exception\InvalidUserDataException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;
use App\Service\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&ValidatorInterface $validator;
    
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->userService = new UserService($this->userRepository, $this->validator);
    }
    
    // =============================================
    // findUserById
    // =============================================
    
    public function testFindUserByIdReturnsUser(): void
    {
        $user = new User();
        $user->setName('Alice');
        $user->setAge(30);
        $user->setEmail('alice@example.com');
        
        $this->userRepository
        ->expects($this->once())
        ->method('findUserById')
        ->with(1)
        ->willReturn($user);
        
        $result = $this->userService->findUserById(1);
        
        $this->assertSame($user, $result);
    }
    
    public function testFindUserByIdThrowsExceptionWhenNotFound(): void
    {
        $this->userRepository
        ->expects($this->once())
        ->method('findUserById')
        ->with(99)
        ->willReturn(null);
        
        $this->expectException(UserNotFoundException::class);
        
        $this->userService->findUserById(99);
    }
    
    // =============================================
    // createUser
    // =============================================
    
    public function testCreateUserSuccess(): void
    {
        $this->validator
        ->expects($this->once())
        ->method('validate')
        ->willReturn(new ConstraintViolationList());
        
        $createdUser = new User();
        $createdUser->setName('Bob');
        $createdUser->setAge(25);
        $createdUser->setEmail('bob@example.com');
        
        $this->userRepository
        ->expects($this->once())
        ->method('createUser')
        ->willReturn($createdUser);
        
        $result = $this->userService->createUser('Bob', 25, 'bob@example.com');
        
        $this->assertSame('Bob', $result->getName());
        $this->assertSame(25, $result->getAge());
    }
    
    public function testCreateUserThrowsExceptionOnValidationFailure(): void
    {
        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Name is required');
        
        $violationList = new ConstraintViolationList([$violation]);
        
        $this->validator
        ->expects($this->once())
        ->method('validate')
        ->willReturn($violationList);
        
        $this->expectException(InvalidUserDataException::class);
        
        $this->userService->createUser('', 0, 'invalid');
    }
    
    // =============================================
    // deactivateUser
    // =============================================
    
    public function testDeactivateUserSuccess(): void
    {
        $user = new User();
        $user->setName('Charlie');
        $user->setAge(28);
        $user->setEmail('charlie@example.com');
        
        $this->userRepository
        ->expects($this->once())
        ->method('findUserById')
        ->with(1)
        ->willReturn($user);
        
        $this->userRepository
        ->expects($this->once())
        ->method('updateUser')
        ->with($user);
        
        $result = $this->userService->deactivateUser(1);
        
        $this->assertFalse($result->isActive());
    }
    
    // =============================================
    // deleteUser
    // =============================================
    
    public function testDeleteUserThrowsExceptionWhenNotFound(): void
    {
        $this->userRepository
        ->expects($this->once())
        ->method('findUserById')
        ->with(99)
        ->willReturn(null);
        
        $this->expectException(UserNotFoundException::class);
        
        $this->userService->deleteUser(99);
    }
}