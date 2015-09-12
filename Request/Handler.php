<?php

namespace Innmind\Rest\Server\Request;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Exception\ResourceNotFoundException;
use Innmind\Rest\Server\Exception\TooManyResourcesFoundException;

class Handler
{
    protected $storages;
    protected $resourceBuilder;

    public function __construct(
        Storages $storages,
        ResourceBuilder $resourceBuilder
    ) {
        $this->storages = $storages;
        $this->resourceBuilder = $resourceBuilder;
    }

    /**
     * List all the resources
     *
     * @param ResourceDefinition $definition
     *
     * @return \SplObjectStorage
     */
    public function indexAction(ResourceDefinition $definition)
    {
        $storage = $this->storages->get($definition->getStorage());

        return $storage->read($definition);
    }

    /**
     * Return a resource
     *
     * @param ResourceDefinition $definition
     * @param mixed $id
     *
     * @return Resource
     */
    public function getAction(ResourceDefinition $definition, $id)
    {
        $storage = $this->storages->get($definition->getStorage());
        $resources = $storage->read($definition, $id);

        if ($resources->count() < 1) {
            throw new ResourceNotFoundException;
        } else if ($resources->count() > 1) {
            throw new TooManyResourcesFoundException;
        }

        return $resources->current();
    }

    /**
     * Create a resource
     *
     * @param Innmind\Rest\Server\Resource $resource
     *
     * @return Resource
     */
    public function createAction(Resource $resource)
    {
        $storage = $this->storages->get(
            $resource->getDefinition()->getStorage()
        );
        $id = $storage->create($resource);
        $resource->set(
            $resource->getDefinition()->getId(),
            $id
        );

        return $resource;
    }

    /**
     * Update a resource
     *
     * @param Innmind\Rest\Server\Resource $resource
     * @param mixed $id
     *
     * @return Resource
     */
    public function updateAction(Resource $resource, $id)
    {
        $storage = $this->storages->get(
            $resource->getDefinition()->getStorage()
        );
        $storage->update($resource, $id);

        return $resource;
    }

    /**
     * Delete a resource
     *
     * @param ResourceDefinition $definition
     * @param mixed $id
     *
     * @return void
     */
    public function deleteAction(ResourceDefinition $definition, $id)
    {
        $storage = $this->storages->get($definition->getStorage());
        $storage->delete($definition, $id);
    }

    /**
     * Format the resource description for the outside world
     * without exposing sensitive data
     *
     * @param ResourceDefinition $definition
     *
     * @return array
     */
    public function optionsAction(ResourceDefinition $definition)
    {
        $output = [
            'id' => $definition->getId(),
            'properties' => [],
        ];

        foreach ($definition->getproperties() as $property) {
            $output['properties'][(string) $property] = [
                'type' => $property->getType(),
                'access' => $property->getAccess(),
                'variants' => $property->getVariants(),
            ];

            if ($property->getType() === 'array') {
                $output['properties'][(string) $property]['inner_type'] = $property->getOption('inner_type');
            }

            if ($property->hasOption('optional')) {
                $output['properties'][(string) $property]['optional'] = true;
            }

            if ($property->containsResource()) {
                $sub = $property->getOption('resource');
                $output['properties'][(string) $property]['resource'] = $sub;
            }
        }

        if ($metas = $definition->getMetas()) {
            $output['meta'] = $metas;
        }

        return ['resource' => $output];
    }
}
