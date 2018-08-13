<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $commandFullName ?>;

/**
 * Class <?= $class_name ?>.
 */
class <?= $class_name ?>

{
    /**
     * @param \<?= $commandFullName ?> $command
     */
    public function __invoke(<?= $commandShortName ?> $command)
    {

    }
}
