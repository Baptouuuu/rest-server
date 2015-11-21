# REST Server

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/build-status/master)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2caf0b66-38d9-4aec-bec4-148b11d9877c/big.png)](https://insight.sensiolabs.com/projects/2caf0b66-38d9-4aec-bec4-148b11d9877c)

Smart library to easily build REST APIs in a descriptive way, you tell what your resources are and it will handle the rest.

The approach here is slightly different from other library used to build APIs in the sense that in general you expose your entities directly through the API (minus some fields in some cases), but you don't want to do that in every case. If you keep a layer of abstraction between your entities and the resources you expose allows a greater flexibility, consequently you'll be less enclined of thinking about versioning as you can change your inner architecture without affecting directly whats exposed to the world.

## Installation

Via composer:

```sh
composer require innmind/rest-server
```

## Architecture

It revolves around this principles:

* resources exposed via a configuration file
* mechanism to translate a resource to an entity, and vice versa
* storage facades
* bunch of events to hook at almost any step

The goal is that by default you only need to write configuration to expose your entities, but if you want to build something more advanced you can by hooking in the system to change default behaviour.

## Setup

```php
use Innmind\Rest\Server\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application(
    '/path/to/config/file.yml',
    '/path/to/config/services.yml'
);
$response = $app->handle(Request::createFromGlobals());
$response->send();
```

So what happens here?!

First you tell the library to load the definitions of your resources (located at `/path/to/config/file.yml`). Then to load your services (at `/path/to/config/services.yml`), which contains the storages definitions (more on that in a bit).

And in the end you call the mechanism to transform the request into a response (and send it).

### Storages

The storages you give to the setup are facades for doctrine (or neo4j) and implements a simple [`StorageInterface`](StorageInterface.php). To build a `DcotrineStorage` you can do so as follows:

```yml
# /path/to/config/service.yml
services:
    my_doctrine:
        parent: storage.abstract.doctrine
        arguments:
            index_0: @doctrine
        tags:
            - { name: storage, alias: dcotrine }

    doctrine:
        class: ... # check the doctrine website to know how to create an instance
```

To build a `Neo4jStorage` you can have a look at this [fixture](fixtures/services/local.yml) (as you can see it is very similar).

### Configuration file

Now is time to create the yaml file to describe your resources. Here's an example:

```yaml
collections:
    blog:
        storage: doctrine
        resources:
            article:
                id: id
                properties:
                    id:
                        type: int
                        access: [READ]
                    title:
                        type: string
                        access: [READ, CREATE, UPDATE]
                    slug:
                        type: string
                        access: [READ, CREATE]
                    content:
                        type: string
                        access: [READ, CREATE, UPDATE]
                    author:
                        type: resource
                        access: [READ]
                        options:
                            resource: author
                options:
                    class: Some\Entity\Article
            author:
                id: id
                properties:
                    id:
                        type: int
                        access: [READ]
                    name:
                        type: string
                        access: [READ, CREATE, UPDATE]
                options:
                    class: Some\Entity\Author
```

So here we have the resources `article` and `author` regrouped under the collection `blog`, basically this will allow to generates routes for `/blog/article/` and `/blog/author/`. Both resources will be persisted via the storage `doctrine` (you can define a storage per resource also).

The `access`es is a simple way to tell which property is allowed in read, create or update action (read `GET`, `POST`, `PUT` verbs).

This will generates the following routes:

* `GET /blog/article/` return all the articles
* `OPTIONS /blog/article/` expose the structure of an article (almost what you've described in the yaml)
* `GET /blog/article/{id}` return the article for the given `id`
* `POST /blog/article/` create a new article
* `PUT /blog/article/{id}` update the article with the given id
* `DELETE /blog/article/{id}` delete the article with the given id
* `GET /blog/author/`
* `OPTIONS /blog/author/`
* `GET /blog/author/{id}`
* `POST /blog/author/`
* `PUT /blog/author/{id}`
* `DELETE /blog/author/{id}`
