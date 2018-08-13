<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?php if ($command): ?>
use Prooph\Bundle\ServiceBus\CommandBus;
<?php endif; ?>
<?php if ($query): ?>
use Prooph\Bundle\ServiceBus\QueryBus;
<?php endif; ?>
use React\Promise\PromiseInterface;

/**
* Class <?= $class_name ?>.
*/
class <?= $class_name ?>

{
<?php if ($command || $query): ?>
<?php if ($command): ?>
    /** @var \Prooph\Bundle\ServiceBus\CommandBus */
    private $commandBus;
<?php endif; ?>

<?php if ($query): ?>
    /** @var \Prooph\Bundle\ServiceBus\QueryBus */
    private $queryBus;
<?php endif; ?>

    /**
<?php if ($command): ?>
     * @param \Prooph\Bundle\ServiceBus\CommandBus $commandBus
<?php endif; ?>
<?php if ($query): ?>
     * @param \Prooph\Bundle\ServiceBus\QueryBus   $queryBus
<?php endif; ?>
     */
    public function __construct(<?php if ($command): ?>CommandBus $commandBus<?php endif; ?><?php if ($command && $query): ?>, <?php endif; ?><?php if ($query): ?>QueryBus $queryBus<?php endif; ?>)
    {
<?php if ($command): ?>
        $this->commandBus = $commandBus;
<?php endif; ?>
<?php if ($query): ?>
        $this->queryBus = $queryBus;
<?php endif; ?>
    }
<?php endif; ?>

    /**
     * @return PromiseInterface
     */
    public function __invoke()
    {

    }
}
