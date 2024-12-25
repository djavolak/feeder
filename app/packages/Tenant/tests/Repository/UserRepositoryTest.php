<?php

namespace Test\EcomHelper\Tenant\Repository;

use PHPUnit\Framework\TestCase;
use Skeletor\User\Mapper\User;
use Skeletor\User\Model\Admin;
use Skeletor\User\Model\UserFactory;
use Skeletor\User\Repository\UserRepository;

class TenantRepositoryTest extends TestCase
{

    public function testGetByEmailEmailShouldReturnNotFoundException()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAll'])
            ->getMock();
        $userMapperMock->expects(static::once())
            ->method('fetchAll')
            ->willReturn(array());
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userFactoryMock = $this->getMockBuilder(UserFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User entity not found.');

        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryMock);
        $email = 'test@example.com';
        $userRepository->getByEmail($email);
    }

    public function testGetByEmailWithValidEmailShouldReturnUserModel()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAll'])
            ->getMock();
        $userMapperMock->expects(static::once())
            ->method('fetchAll')
            ->willReturn([[
                'email' => 'test@example.com',
                'userId' => '1',
                'ipv4' => '123',
                'lastLogin' => '123',
                'password' => 'test123',
                'displayName' => 'test',
                'isActive' => 1,
                'role' => 1
            ]]);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userFactoryStub = new UserFactory(new \DateTime());

        $dt = new \DateTime();
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $email = 'test@example.com';
        $this->assertInstanceOf('\Skeletor\User\Model\User', $userRepository->getByEmail($email));
    }

    public function testGetByIdShouldReturnUserModel()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchById', 'fetchAll'])
            ->getMock();
        $userMapperMock->expects(static::exactly(1))
            ->method('fetchById')
            ->willReturn([
                'email' => 'test@example.com',
                'userId' => '1',
                'password' => 'test123',
                'displayName' => 'test',
                'isActive' => 1,
                'role' => 1,
                'ipv4' => '123',
                'lastLogin' => '123',
                'createdAt' =>'',
                'updatedAt' => '',
            ]);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $this->assertInstanceOf('\Skeletor\User\Model\User', $userRepository->getById(1));
    }

    public function testFetchAllShouldReturnArrayOfUserModels()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAll'])
            ->getMock();
        $userMapperMock->expects(static::once())
            ->method('fetchAll')
            ->willReturn([[
                'email' => 'test@example.com',
                'userId' => '1',
                'password' => 'test123',
                'ipv4' => '123',
                'lastLogin' => '123',
                'displayName' => 'test',
                'isActive' => 1,
                'role' => 1
            ]]);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $this->assertInstanceOf('\Skeletor\User\Model\User', $userRepository->fetchAll()[0]);
    }

    public function testCreateShouldReturnUserModel()
    {
        $userData = [
            'email' => 'test@example.com',
            'userId' => '1',
            'password' => 'test123',
            'ipv4' => '123',
            'lastLogin' => '123',
            'displayName' => 'test',
            'isActive' => 1,
            'role' => 1
        ];
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'fetchById'])
            ->getMock();
        $userMapperMock->expects(static::once())
            ->method('insert')
            ->willReturn(1);
        $userMapperMock->expects(static::once())
            ->method('fetchById')
            ->willReturn($userData);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $this->assertInstanceOf(Admin::class, $userRepository->create(['password' => 'test123']));
    }

    public function testUpdateShouldReturnUserModel()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchById', 'update'])
            ->getMock();
        $userMapperMock->expects(static::exactly(1))
            ->method('update')
            ->willReturn(1);
        $userMapperMock->expects(static::exactly(1))
            ->method('fetchById')
            ->willReturn([
                'email' => 'test@example.com',
                'userId' => '1',
                'password' => 'test123',
                'displayName' => 'test',
                'isActive' => 1,
                'ipv4' => '123',
                'lastLogin' => '123',
                'role' => 2,
                'createdAt' =>'',
                'updatedAt' => '',
            ]);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $userData = [
            'password' => 'test123',
            'role' => 2,
            'email' => 'test@example.com',
            'ipv4' => '123',
            'lastLogin' => '123',
            'userId' => 1,
            'isActive' => 1,
            'displayName' => 'test',
        ];
        $user = $userRepository->update($userData);
        $this->assertInstanceOf(\Skeletor\User\Model\User::class, $user);
        $this->assertSame('/admin/user/view/', $user->getRedirectPath());
        $this->assertSame('test@example.com', $user->getEmail());
        $user::hrLevels();
    }

    public function testUpdateShouldReturnExistingUserModel()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchById', 'update'])
            ->getMock();
        $userMapperMock->expects(static::exactly(1))
            ->method('update')
            ->willReturn(1);
        $userMapperMock->expects(static::exactly(2))
            ->method('fetchById')
            ->willReturn([
                'email' => 'test@example.com',
                'userId' => '1',
                'password' => 'test123',
                'displayName' => 'test',
                'isActive' => 1,
                'ipv4' => '123',
                'lastLogin' => '123',
                'role' => 2,
                'createdAt' =>'',
                'updatedAt' => '',
            ]);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $userData = [
            'password' => '',
            'role' => 1,
            'email' => 'test@example.com',
            'userId' => 1,
            'isActive' => 1,
            'ipv4' => '123',
            'lastLogin' => '123',
            'displayName' => 'test',
        ];
        $this->assertInstanceOf(\Skeletor\User\Model\User::class, $userRepository->update($userData));
    }

    public function testDeleteUserShouldReturnBool()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['delete'])
            ->getMock();
        $userMapperMock->expects(static::once())
            ->method('delete')
            ->willReturn(true);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $this->assertSame(true, $userRepository->delete(1));
    }

    public function testMakeWillThrowException()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchById'])
            ->getMock();
        $userMapperMock->expects(static::exactly(1))
            ->method('fetchById')
            ->willReturn([
                'email' => 'test@example.com',
                'userId' => '1',
                'password' => 'test123',
                'displayName' => 'test',
                'isActive' => 1,
                'ipv4' => '123',
                'lastLogin' => '123',
                'role' => 0,
                'createdAt' =>'',
                'updatedAt' => '',
            ]);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid role provided');
        $user = $userRepository->getById(1);
        $this->assertInstanceOf(\Skeletor\User\Model\User::class, $user);
    }

    public function testEmailExistsWillReturnFalse()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAll'])
            ->getMock();
        $userMapperMock->expects(static::once())
            ->method('fetchAll')
            ->willThrowException(new \Exception('test'));
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $this->assertSame(false, $userRepository->emailExists('test'));
    }

    public function testEmailExistsWillReturnTrue()
    {
        $userMapperMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAll'])
            ->getMock();
        $userMapperMock->expects(static::once())
            ->method('fetchAll')
            ->willReturn([[
                'email' => 'test@example.com',
                'userId' => '1',
                'password' => 'test123',
                'displayName' => 'test',
                'createdAt' => '2020-01-01 00:00:00',
                'isActive' => 1,
                'ipv4' => '123',
                'lastLogin' => '123',
                'role' => 1
            ]]);
        $configMock = $this->getMockBuilder(\Laminas\Config\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dt = new \DateTime();
        $userFactoryStub = new UserFactory($dt);
        $userRepository = new UserRepository($userMapperMock, $dt, $configMock, $userFactoryStub);
        $this->assertSame(true, $userRepository->emailExists('test'));
    }
}
