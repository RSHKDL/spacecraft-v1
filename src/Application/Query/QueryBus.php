<?php

declare(strict_types=1);

namespace App\Application\Query;

use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class QueryBus
{
    use HandleTrait;

    public function __construct(#[Target('query.bus')]MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    /**
     * The handler's return type is not checked against TResult: integration
     * tests cover that link. For information, the main goal of Template TResult
     * is to pass PHPStan level 9, to be strict about explicit mixed type.
     *
     * @template TResult
     * @param Query<TResult> $query
     * @return TResult
     */
    public function ask(Query $query): mixed
    {
        return $this->handle($query);
    }
}
