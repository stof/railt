<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Foundation\Webonyx;

use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Error\SyntaxError;
use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Type\Schema;
use Railt\Container\Exception\ContainerResolutionException;
use Railt\Foundation\ApplicationInterface;
use Railt\Foundation\Webonyx\Builder\SchemaBuilder;
use Railt\Foundation\Webonyx\Exception\WebonyxException;
use Railt\Http\Exception\GraphQLException;
use Railt\Http\Exception\GraphQLExceptionInterface;
use Railt\Http\Identifiable;
use Railt\Http\RequestInterface;
use Railt\Http\Response;
use Railt\Http\ResponseInterface;
use Railt\SDL\Contracts\Definitions\SchemaDefinition;
use Railt\SDL\Reflection\Dictionary;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Connection
 */
class Connection
{
    /**
     * @var TypeLoader
     */
    private $loader;

    /**
     * @var SchemaDefinition
     */
    private $schema;

    /**
     * @var Dictionary
     */
    private $dictionary;

    /**
     * @var ApplicationInterface
     */
    private $app;

    /**
     * Connection constructor.
     *
     * @param ApplicationInterface $app
     * @param Dictionary $dictionary
     * @param SchemaDefinition $schema
     */
    public function __construct(ApplicationInterface $app, Dictionary $dictionary, SchemaDefinition $schema)
    {
        $this->app = $app;
        $this->schema = $schema;
        $this->dictionary = $dictionary;
    }

    /**
     * @return TypeLoader
     * @throws ContainerResolutionException
     */
    private function getTypeLoader(): TypeLoader
    {
        if ($this->loader === null) {
            $this->loader = new TypeLoader($this->getEventDispatcher(), $this->dictionary);
        }

        return $this->loader;
    }

    /**
     * @return bool
     */
    private function isDebug(): bool
    {
        return $this->app->isDebug();
    }

    /**
     * @return EventDispatcherInterface
     * @throws ContainerResolutionException
     */
    private function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->app->getContainer()
            ->make(EventDispatcherInterface::class);
    }

    /**
     * @param Identifiable $connection
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function request(Identifiable $connection, RequestInterface $request): ResponseInterface
    {
        try {
            /** @var ExecutionResult $result */
            $result = $this->exec($connection, $request, $this->getSchema($this->schema, $this->getTypeLoader()));

            return $this->createResponse($result->errors, $result->data);
        } catch (\Throwable $e) {
            return $this->createResponse([$e]);
        }
    }

    /**
     * @param Identifiable $connection
     * @param RequestInterface $request
     * @param Schema $schema
     * @return mixed
     * @throws InvariantViolation
     */
    private function exec(Identifiable $connection, RequestInterface $request, Schema $schema)
    {
        if ($this->isDebug()) {
            $schema->assertValid();
        }

        $executor = $this->getExecutor($connection, $schema);

        return $executor($request);
    }

    /**
     * @param Identifiable $connection
     * @param Schema $schema
     * @return \Closure
     */
    private function getExecutor(Identifiable $connection, Schema $schema): \Closure
    {
        return function (RequestInterface $request) use ($connection, $schema) {
            $vars = $request->getVariables();
            $query = $this->parse($request->getQuery());

            $this->analyzeRequest($request, $query, $operation = $request->getOperation());

            $context = new Context($connection, $request);

            return GraphQL::executeQuery($schema, $query, null, $context, $vars, $operation);
        };
    }

    /**
     * @param string $query
     * @return DocumentNode
     * @throws SyntaxError
     */
    private function parse(string $query): DocumentNode
    {
        return Parser::parse(new Source($query ?: '', 'GraphQL'));
    }

    /**
     * @param RequestInterface $request
     * @param DocumentNode $ast
     * @param string|null $operation
     */
    private function analyzeRequest(RequestInterface $request, DocumentNode $ast, string $operation = null): void
    {
        /** @var OperationDefinitionNode $node */
        foreach ($ast->definitions as $node) {
            if ($node->kind === 'OperationDefinition') {
                $realOperationName = $this->readQueryName($node);

                if ($operation === $realOperationName) {
                    $request->withOperation($realOperationName);
                    $request->withQueryType($node->operation);

                    return;
                }
            }
        }
    }

    /**
     * @param OperationDefinitionNode $operation
     * @return string|null
     */
    private function readQueryName(OperationDefinitionNode $operation): ?string
    {
        if ($operation->name === null) {
            return null;
        }

        return (string)$operation->name->value;
    }

    /**
     * @param SchemaDefinition $schema
     * @param TypeLoader $loader
     * @return Schema
     * @throws ContainerResolutionException
     */
    private function getSchema(SchemaDefinition $schema, TypeLoader $loader): Schema
    {
        $builder = new SchemaBuilder($schema, $this->getEventDispatcher(), $loader);

        $builder->preload($this->dictionary);

        return $builder->build();
    }

    /**
     * @param iterable $exceptions
     * @param null $data
     * @return ResponseInterface
     */
    private function createResponse(iterable $exceptions, $data = null): ResponseInterface
    {
        $response = new Response($data);

        foreach ($this->wrapExceptions($exceptions) as $exception) {
            $response->withException($exception);
        }

        return $response;
    }

    /**
     * @param iterable|\Throwable[] $exceptions
     * @return \Generator|GraphQLExceptionInterface[]
     */
    private function wrapExceptions(iterable $exceptions): \Generator
    {
        foreach ($exceptions as $exception) {
            $exception = $this->wrapException($exception);

            yield $exception;
        }
    }

    /**
     * @param \Throwable $exception
     * @return GraphQLExceptionInterface
     */
    private function wrapException(\Throwable $exception): GraphQLExceptionInterface
    {
        if ($exception instanceof Error) {
            return new WebonyxException($exception);
        }

        $message = $exception->getMessage();

        if ($exception instanceof InvariantViolation) {
            $message = 'Schema Exception: ' . $message;
        }

        return new GraphQLException($message, $exception->getCode(), $exception);
    }
}
