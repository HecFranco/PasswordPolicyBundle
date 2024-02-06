<?php


namespace HecFranco\PasswordPolicyBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
// events
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
// attributes
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
//
use HecFranco\PasswordPolicyBundle\Exceptions\RuntimeException;
use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use HecFranco\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface;

#[AsDoctrineListener(event: Events::onFlush, priority: 500, connection: 'default')]
class PasswordEntityListener
{
  /**
   * @var string
   */
  private $passwordField;
  /**
   * @var string
   */
  private $passwordHistoryField;
  /**
   * @var int
   */
  private $historyLimit;

  /**
   * @var string
   */
  private $entityClass;

  /**
   * @var array
   */
  private $processedNewEntities = [];

  /**
   * @var array
   */
  private $processedPasswords = [];

  /**
   * PasswordEntityListener constructor.
   * @param \HecFranco\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface $passwordHistoryService
   * @param string $passwordField
   * @param string $passwordHistoryField
   * @param int $historyLimit
   * @param string $entityClass
   */
  public function __construct(
    public PasswordHistoryServiceInterface $passwordHistoryService,
    string $passwordField,
    string $passwordHistoryField,
    int $historyLimit,
    string $entityClass
  ) {
    $this->passwordField = $passwordField;
    $this->passwordHistoryField = $passwordHistoryField;
    $this->historyLimit = $historyLimit;
    $this->entityClass = $entityClass;
  }

  #[ORM\OnFlush]
  public function onFlush(OnFlushEventArgs $eventArgs): void
  {
    $em = $eventArgs->getObjectManager();
    $unitOfWork = $em->getUnitOfWork();
    //
    foreach ($unitOfWork->getIdentityMap() as $entities) {
      foreach($entities as $entity){
        if (is_a($entity, $this->entityClass, true) && $entity instanceof HasPasswordPolicyInterface) {
          $changeSet = $unitOfWork->getEntityChangeSet($entity);
          if (array_key_exists($this->passwordField, $changeSet) && array_key_exists(
            0,
            $changeSet[$this->passwordField]
          )) {
            $this->createPasswordHistory($em, $entity, $changeSet[$this->passwordField][0]);
          }
        }
      }
    }
  }

/**
 * The function `createPasswordHistory` creates a new password history entry for a given entity,
 * storing the old password and associating it with the entity.
 *
 * @param EntityManagerInterface em EntityManagerInterface object, used for managing entities and
 * performing database operations.
 * @param HasPasswordPolicyInterface entity The `entity` parameter is an object that implements the
 * `HasPasswordPolicyInterface` interface. It represents the entity for which the password history is
 * being created.
 * @param oldPassword The `oldPassword` parameter is a nullable string that represents the previous
 * password of the entity. If it is null or an empty string, the method will use the current password
 * of the entity.
 *
 * @return ?PasswordHistoryInterface an instance of the PasswordHistoryInterface or null.
 */
  public function createPasswordHistory(
    EntityManagerInterface $em,
    HasPasswordPolicyInterface $entity,
    ?string $oldPassword
  ): ?PasswordHistoryInterface {
    if (is_null($oldPassword) || $oldPassword === '') {
      $oldPassword = $entity->getPassword();
    }
    //
    if (!$oldPassword) {
      return null;
    }
    //
    if (array_key_exists($oldPassword, $this->processedPasswords)) {
      return null;
    }
    //
    $unitOfWork = $em->getUnitOfWork();
    $entityMeta = $em->getClassMetadata(get_class($entity));
    //
    $historyClass = $entityMeta->associationMappings[$this->passwordHistoryField]['targetEntity'];
    $mappedField = $entityMeta->associationMappings[$this->passwordHistoryField]['mappedBy'];
    //
    $history = new $historyClass();
    // Check if the history class implements the PasswordHistoryInterface interface.
    if (!$history instanceof PasswordHistoryInterface) {
      throw new RuntimeException(sprintf(
        '%s must implement %s',
        $historyClass,
        PasswordHistoryInterface::class
      ));
    }
    //
    $userSetter = 'set' . ucfirst($mappedField);
    // Check if the history class has a setter method for the user relation.
    if (!method_exists($history, $userSetter)) {
      throw new RuntimeException(sprintf(
        'Cannot set user relation in password history class %s because method %s is missing',
        $historyClass,
        $userSetter
      ));
    }
    //
    $history->$userSetter($entity);
    $history->setPassword($oldPassword);
    $history->setCreatedAt(new \DateTime());
    // $history->setSalt($entity->getSalt());
    //
    $entity->addPasswordHistory($history);

    $this->processedPasswords[$oldPassword] = $history;

    $stalePasswords = $this->passwordHistoryService->getHistoryItemsForCleanup($entity, $this->historyLimit);

    foreach ($stalePasswords as $stalePassword) {
      $em->remove($stalePassword);
    }

    $em->persist($history);

    $metadata = $em->getClassMetadata($historyClass);
    $unitOfWork->computeChangeSet($metadata, $history);

    $entity->setPasswordChangedAt(new \DateTime());
    // We need to recompute the change set so we won't trigger updates instead of inserts.
    $unitOfWork->recomputeSingleEntityChangeSet($entityMeta, $entity);

    return $history;
  }
}
