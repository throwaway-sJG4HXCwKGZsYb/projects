<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class AppUserProvider implements UserProviderInterface
{
    /**
     * @throws \Exception
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Hardcoded user data
        if ($identifier === 'admin') {
            return new AppUser('admin', '$2y$13$NRaTM5uZ9QjX4wlowMs6L.YASJITiczcdEkPEpV9/X16g1n3VbEoy'); // Password: 'password'
        }

        throw new \Exception('User not found.');
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AppUser) {
            throw new UnsupportedUserException('Unsupported user type.');
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return AppUser::class === $class;
    }
}
