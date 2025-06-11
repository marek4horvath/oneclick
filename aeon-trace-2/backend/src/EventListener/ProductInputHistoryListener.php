<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Product\ProductInput;
use App\Entity\Product\ProductInputHistory;
use App\Entity\User;
use App\Utils\CollectionUtils;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[AsDoctrineListener(
    event: Events::onFlush,
)]
readonly class ProductInputHistoryListener implements EventSubscriber
{
    public function __construct(
        private Security $security,
        private RequestStack $requestStack,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !in_array(strtoupper($request->getMethod()), ['PATCH', 'POST', 'DELETE'], true)) {
            return;
        }

        $entityManager = $eventArgs->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        /** @var User|null $user */
        $user = $this->security->getUser();

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof ProductInput) {
                continue;
            }

            if (!$user instanceof User) {
                throw new RuntimeException('User was not found in database.', Response::HTTP_NOT_FOUND);
            }

            $type = $entity->getType();

            if ($type === null) {
                throw new RuntimeException('Could not find input type. Check if the input you are trying to edit has type declared.', Response::HTTP_NOT_FOUND);
            }

            $imagesChanged = false;
            $images = null;

            foreach ($unitOfWork->getScheduledCollectionUpdates() as $collection) {
                if ($collection->getOwner() === $entity && $collection->getMapping()['fieldName'] === 'images') {
                    $imagesChanged = true;
                    $images = $collection;
                    break;
                }
            }

            $changeSet = $unitOfWork->getEntityChangeSet($entity);

            $date = new DateTimeImmutable();

            $newHistoryInput = new ProductInputHistory();
            $newHistoryInput->setProductInput($entity);
            $newHistoryInput->setUpdatedAt($date);
            $newHistoryInput->setCreatedAt($entity->getUpdatedAt());
            $newHistoryInput->setUpdatedBy($user);
            $newHistoryInput->setName($entity->getName());
            $newHistoryInput->setType($type);
            $newHistoryInput->setVersion($entity->getVersion());
            $newHistoryInput->setLocked($entity->isLocked());
            $newHistoryInput->setAutomaticCalculation($entity->isAutomaticCalculation());
            $newHistoryInput->setUnitMeasurement($entity->getUnitMeasurement());
            $newHistoryInput->setMeasurementType($entity->getMeasurementType());
            $newHistoryInput->setMeasurementValue($entity->getMeasurementValue());
            $newHistoryInput->setUnitSymbol($entity->getUnitSymbol());

            if(array_key_exists('measurementValue', $changeSet)) {
                $newHistoryInput->setMeasurementValue(is_numeric($changeSet['measurementValue'][0]) ? (float) $changeSet['measurementValue'][0] : null);
            }

            switch ($type) {
                case 'text':
                    if (array_key_exists('textValue', $changeSet)) {
                        /** @var string|null $value */
                        $value = $changeSet['textValue'][0];

                        $newHistoryInput->setTextValue($value);
                    }
                    break;

                case 'textarea':
                    if (array_key_exists('textAreaValue', $changeSet)) {
                        /** @var string|null $value */
                        $value = $changeSet['textAreaValue'][0];

                        $newHistoryInput->setTextAreaValue($value);
                    }
                    break;

                case 'numerical':
                    if (array_key_exists('numericalValue', $changeSet)) {
                        /** @var float|null $value */
                        $value = $changeSet['numericalValue'][0];

                        $newHistoryInput->setNumericalValue($value);
                    }
                    break;

                case 'dateTime':
                    if (array_key_exists('dateTimeTo', $changeSet)) {
                        /** @var DateTime|null $value */
                        $value = $changeSet['dateTimeTo'][0];

                        $newHistoryInput->setDateTimeTo($value);
                    }
                    break;

                case 'dateTimeRange':
                    if (array_key_exists('dateTimeFrom', $changeSet) && array_key_exists('dateTimeTo', $changeSet)) {
                        /** @var DateTime|null $valueFrom */
                        $valueFrom = $changeSet['dateTimeFrom'][0];

                        /** @var DateTime|null $valueTo */
                        $valueTo = $changeSet['dateTimeTo'][0];

                        $newHistoryInput->setDateTimeFrom($valueFrom);
                        $newHistoryInput->setDateTimeTo($valueTo);
                    }
                    break;

                case 'coordinates':
                    if (array_key_exists('latitudeValue', $changeSet) && array_key_exists('longitudeValue', $changeSet)) {
                        /** @var float|null $lat */
                        $lat = $changeSet['latitudeValue'][0];

                        /** @var float|null $lng */
                        $lng = $changeSet['longitudeValue'][0];

                        $newHistoryInput->setLatitudeValue($lat);
                        $newHistoryInput->setLongitudeValue($lng);
                    }
                    break;

                case 'image':
                    if (array_key_exists('image', $changeSet)) {
                        /** @var string|null $value */
                        $value = $changeSet['image'][0];

                        $newHistoryInput->setImage($value);
                    }
                    break;

                case 'images':
                    if ($imagesChanged && $images !== null) {
                        $originalImages = CollectionUtils::reconstructOriginalCollection($images);

                        foreach ($originalImages as $image) {
                            $newHistoryInput->addImage($image);
                        }
                    }
                    break;

                case 'file':
                    if (array_key_exists('document', $changeSet)) {
                        /** @var string|null $value */
                        $value = $changeSet['document'][0];

                        $newHistoryInput->setDocument($value);
                    }
                    break;

                case 'checkboxList':

                    if (array_key_exists('checkboxValue', $changeSet)) {
                        /** @var array<string>|null $value */
                        $value = $changeSet['checkboxValue'][0];

                        $newHistoryInput->setCheckboxValue($value);
                    }
                    break;

                case 'radioList':

                    if (array_key_exists('radioValue', $changeSet)) {
                        /** @var string|null $value */
                        $value = $changeSet['radioValue'][0];

                        $newHistoryInput->setRadioValue($value);
                    }
                    break;
            }

            $entityManager->persist($newHistoryInput);

            $unitOfWork->computeChangeSet(
                $entityManager->getClassMetadata(ProductInputHistory::class),
                $newHistoryInput
            );

            // Update Product Input
            $entity->setUpdatedAt($date);
            $entity->setUpdatedBy($user);
            $entity->setVersion($entity->getVersion() + 1);

            $unitOfWork->recomputeSingleEntityChangeSet(
                $entityManager->getClassMetadata(ProductInput::class),
                $entity
            );
        }
    }
}
