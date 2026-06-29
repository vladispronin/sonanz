<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use ReflectionProperty;

#[AsDoctrineListener(event: Events::onFlush)]
final class TimestampableListener
{
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em  = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!property_exists($entity, 'updatedAt')) {
                continue;
            }

            $prop = new ReflectionProperty($entity, 'updatedAt');
            $prop->setValue($entity, new DateTimeImmutable());

            $uow->recomputeSingleEntityChangeSet(
                $em->getClassMetadata($entity::class),
                $entity
            );
        }
    }
}
