<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class <?= $class_name ?>.
 */
class <?= $class_name ?>

{
    const SQL_STATE_PARTITION_ERROR = '23514';

    /**
     * Entity manager to be used by all extending repositories.
     *
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * AbstractRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @var string
     *
     * @return EntityManager
     */
    protected function getEntityManager($class): EntityManager
    {
        $entityManager = $this->registry->getEntityManagerForClass($class);

        if (null === $entityManager) {
            throw new \LogicException('No entity manager for class '.$class);
        }

        return $entityManager;
    }
}
