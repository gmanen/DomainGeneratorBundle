<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $queryFullName ?>;
use React\Promise\Deferred;

/**
 * Class <?= $class_name ?>.
 */
class <?= $class_name ?>

{
    /**
     * @param \<?= $queryFullName ?> $query
     * @param \React\Promise\Deferred $deferred
     */
    public function __invoke(<?= $queryShortName ?> $query, Deferred $deferred)
    {
        $deferred->resolve();
    }
}
