<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Event\ResourceBuildEvent;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResourceBuilder
{
    protected $accessor;
    protected $dispatcher;

    public function __construct(
        PropertyAccessorInterface $accessor,
        EventDispatcherInterface $dispatcher
    ) {
        $this->accessor = $accessor;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Build a resource object from a raw data
     * object following the given description
     *
     * @param object $data
     * @param ResourceDefinition $definition
     *
     * @throws InvalidArgumentException If the data is not an object
     * @throws NoSuchPropertyException If a property is not found in the data
     *
     * @return Resource
     */
    public function build($data, ResourceDefinition $definition)
    {
        $this->dispatcher->dispatch(
            Events::RESOURCE_BUILD,
            $event = new ResourceBuildEvent($data, $definition)
        );

        if ($event->hasResource()) {
            return $event->getResource();
        }

        $data = $event->getData();

        if (!is_object($data)) {
            throw new \InvalidArgumentException(sprintf(
                'You must give a data object in order to build the resource %s',
                $definition
            ));
        }

        $resource = new Resource;
        $resource->setDefinition($definition);

        foreach ($definition->getProperties() as $property) {
            if (!$this->accessor->isReadable($data, (string) $property)) {
                continue;
            }

            $value = $this->accessor->getValue($data, (string) $property);

            if ($property->hasOption('optional') && $value === null) {
                continue;
            }

            if ($property->getType() === 'resource') {
                $value = $this->build($value, $property->getOption('resource'));
            } else if (
                $property->getType() === 'array' &&
                $property->getOption('inner_type') === 'resource'
            ) {
                $coll = new Collection;
                foreach ($value as $subValue) {
                    $coll[] = $this->build(
                        $subValue,
                        $property->getOption('resource')
                    );
                }
                $value = $coll;
            } else if (
                $property->getType() === 'date' &&
                $value instanceof \DateTime
            ) {
                $format = $property->hasOption('format') ?
                    $property->getOption('format') : \DateTime::ISO8601;
                $value = $value->format($format);
            }

            $resource->set($property, $value);
        }

        return $resource;
    }
}
