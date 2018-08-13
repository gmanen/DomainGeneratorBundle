<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entityClassName ?>;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Class <?= $class_name ?>.
 */
class <?= $class_name ?> implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * @param \<?= $entityClassName ?> $object
     * @param string $format
     * @param array $context
     *
     * @return array
     * @throws \Exception
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [];
    }

    /**
     * @param mixed  $data
     * @param string $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'json' == $format && $data instanceof <?= $entityShortName ?>;
    }
}
