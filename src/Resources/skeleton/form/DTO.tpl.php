<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
* Class <?= $class_name ?>.
*/
class <?= $class_name ?>

{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     * @param \Symfony\Component\Validator\Mapping\ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

    }
}
