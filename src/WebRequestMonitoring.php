<?php

namespace Palzin\Slim;


use Palzin\Palzin;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class WebRequestMonitoring implements MiddlewareInterface
{
    /**
     * The Palzin instance.
     *
     * @var Palzin
     */
    protected $palzin;

    /**
     * WebRequestMonitoring constructor.
     *
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->palzin = $container->get('palzin');
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);

        $transaction = $this->palzin->startTransaction(
            $request->getMethod() . ' ' . $routeContext->getRoute()->getPattern()
        )->addContext('Request Body', $request->getBody());

        try {
            $response = $handler->handle($request);
        } catch (\Throwable $exception) {
            $this->palzin->reportException($exception, false);
            $transaction->setResult(500);
            throw $exception;
        }

        $transaction->addContext('Response', [
            'headers' => $response->getHeaders(),
        ]);

        $transaction->setResult($response->getStatusCode());

        return $response;
    }
}
