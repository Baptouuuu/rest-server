<?php

namespace Innmind\Rest\Server\Tests\EventListener;

use Innmind\Rest\Server\EventListener\ExceptionListener;
use Innmind\Rest\Server\Exception\PayloadException;
use Innmind\Rest\Server\Exception\ValidationException;
use Innmind\Rest\Server\Exception\ResourceNotFoundException;
use Innmind\Rest\Server\Exception\TooManyResourcesFoundException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Psr\Log\NullLogger;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $kernel;

    public function setUp()
    {
        $this->l = new ExceptionListener(new NullLogger);

        $this->kernel = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::EXCEPTION => 'buildHttpException'],
            ExceptionListener::getSubscribedEvents()
        );
    }

    public function testBuildBadRequest()
    {
        $ev = new GetResponseForExceptionEvent(
            $this->kernel,
            new Request,
            HttpKernel::MASTER_REQUEST,
            new PayloadException
        );
        $this->l->buildHttpException($ev);
        $this->assertInstanceOf(
            BadRequestHttpException::class,
            $ev->getException()
        );

        $ev->setException(new ValidationException);
        $this->l->buildHttpException($ev);
        $this->assertInstanceOf(
            BadRequestHttpException::class,
            $ev->getException()
        );
    }

    public function testBuild520Exception()
    {
        $v = $this->getMock(ConstraintViolationListInterface::class);
        $ev = new GetResponseForExceptionEvent(
            $this->kernel,
            new Request,
            HttpKernel::MASTER_REQUEST,
            ValidationException::build('READ', $v)
        );
        $this->l->buildHttpException($ev);
        $this->assertInstanceOf(
            HttpException::class,
            $ev->getException()
        );
    }

    public function testBuildNotFound()
    {
        $ev = new GetResponseForExceptionEvent(
            $this->kernel,
            new Request,
            HttpKernel::MASTER_REQUEST,
            new ResourceNotFoundException
        );
        $this->l->buildHttpException($ev);
        $this->assertInstanceOf(
            NotFoundHttpException::class,
            $ev->getException()
        );
    }

    public function testBuildConflict()
    {
        $ev = new GetResponseForExceptionEvent(
            $this->kernel,
            new Request,
            HttpKernel::MASTER_REQUEST,
            new TooManyResourcesFoundException
        );
        $this->l->buildHttpException($ev);
        $this->assertInstanceOf(
            ConflictHttpException::class,
            $ev->getException()
        );
    }
}
