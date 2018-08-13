<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

/**
 * Class <?= $class_name ?>.
 */
class <?= $class_name ?> extends Command implements PayloadConstructable, Message
{
    use PayloadTrait;
}
