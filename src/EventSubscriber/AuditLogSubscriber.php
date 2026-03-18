<?php

namespace App\EventSubscriber;

use App\Entity\AuditLog;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bundle\SecurityBundle\Security;

class AuditLogSubscriber implements EventSubscriber
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::onFlush];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        if (!$em instanceof EntityManagerInterface) {
            return;
        }

        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$this->shouldLog($entity)) {
                continue;
            }

            $this->createLog($em, $uow, $entity, 'CREATE', [
                'state' => $this->extractState($em, $entity),
            ]);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$this->shouldLog($entity)) {
                continue;
            }

            $changes = [];
            foreach ($uow->getEntityChangeSet($entity) as $field => [$oldValue, $newValue]) {
                if ($this->isSensitiveField($field)) {
                    $changes[$field] = ['old' => '[REDACTED]', 'new' => '[REDACTED]'];
                    continue;
                }

                $changes[$field] = [
                    'old' => $this->normalizeValue($oldValue),
                    'new' => $this->normalizeValue($newValue),
                ];
            }

            if ($changes === []) {
                continue;
            }

            $this->createLog($em, $uow, $entity, 'UPDATE', ['changes' => $changes]);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$this->shouldLog($entity)) {
                continue;
            }

            $this->createLog($em, $uow, $entity, 'DELETE', [
                'state' => $this->extractState($em, $entity),
            ]);
        }
    }

    private function shouldLog(object $entity): bool
    {
        return !$entity instanceof AuditLog && str_starts_with($entity::class, 'App\\Entity\\');
    }

    private function createLog(
        EntityManagerInterface $em,
        UnitOfWork $uow,
        object $entity,
        string $action,
        ?array $data = null
    ): void {
        $entityMetadata = $em->getClassMetadata($entity::class);
        $identifierValues = $entityMetadata->getIdentifierValues($entity);

        $entityId = null;
        if ($identifierValues !== []) {
            $normalized = array_map(fn (mixed $value): string => (string) $this->normalizeValue($value), $identifierValues);
            $entityId = implode('-', $normalized);
        }

        $log = (new AuditLog())
            ->setEntityClass($entity::class)
            ->setEntityId($entityId)
            ->setAction($action)
            ->setUserEmail($this->security->getUser()?->getUserIdentifier())
            ->setData($data);

        $em->persist($log);
        $uow->computeChangeSet($em->getClassMetadata(AuditLog::class), $log);
    }

    private function extractState(EntityManagerInterface $em, object $entity): array
    {
        $metadata = $em->getClassMetadata($entity::class);
        $state = [];

        foreach ($metadata->getFieldNames() as $fieldName) {
            $value = $metadata->getFieldValue($entity, $fieldName);
            if ($this->isSensitiveField($fieldName)) {
                $state[$fieldName] = '[REDACTED]';
                continue;
            }

            $state[$fieldName] = $this->normalizeValue($value);
        }

        return $state;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        if (is_object($value)) {
            if (method_exists($value, 'getId')) {
                return sprintf('%s#%s', $value::class, (string) ($value->getId() ?? 'null'));
            }

            return $value::class;
        }

        return (string) $value;
    }

    private function isSensitiveField(string $fieldName): bool
    {
        return in_array($fieldName, ['password', 'plainPassword'], true);
    }
}
