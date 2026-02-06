<?php
/**
 * vim:ft=php et ts=4 sts=4
 * @version
 * @todo
 */

namespace App\EventListener;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: User::class)]
// #[AsEntityListener(event: Events::postPersist, entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, entity: User::class)]
class UserListener extends AbstractController
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function prePersist(User $user, LifecycleEventArgs $event): void
    {
        self::updateGidForAdmin($user);
    }

    public function postPersist(User $user, LifecycleEventArgs $event): void
    {
    }
    
    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('roles')) {
            self::updateGidForAdmin($user);
        }
    }
    
    private static function updateGidForAdmin(User $user): void
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $user->setGid(200);
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $user->setGid(100);
        }

        if (in_array('ROLE_ROOT', $user->getRoles())) {
            $user->setGid(0);
        }
    }
}
