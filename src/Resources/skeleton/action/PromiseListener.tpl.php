<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Serializer\SerializerInterface;
use React\Promise\PromiseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class <?= $class_name ?>.
 */
class <?= $class_name ?>

{
    /** @var SerializerInterface */
    private $serializer;

    /** @var string */
    private $environment;

    /**
     * PromiseListener constructor.
     *
     * @param SerializerInterface $serializer
     * @param string              $environment
     */
    public function __construct(SerializerInterface $serializer, string $environment)
    {
        $this->serializer = $serializer;
        $this->environment = $environment;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $val = $event->getControllerResult();
        if (!$val instanceof PromiseInterface) {
            return;
        }

        $response = new JsonResponse();
        $serializer = $this->serializer;

        $environment = $this->environment;

        $val->then(
            function ($result) use ($response, $serializer, $environment) {
                try {
                    $response->setContent($serializer->serialize($result, 'json'));
                } catch (\Throwable $e) {
                    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                    $response->setContent(
                        $serializer->serialize(
                            $this->resolveExceptionMessage(
                                $e->getMessage(),
                                Response::HTTP_INTERNAL_SERVER_ERROR,
                                $environment
                            ),
                            'json'
                        )
                    );
                }
            },
            function ($reason) use ($response, $serializer, $environment) {
                $response->setStatusCode(Response::HTTP_NOT_FOUND);
                $response->setContent(
                    $serializer->serialize(
                        $this->resolveExceptionMessage(
                            $reason->getPrevious()->getMessage(),
                            $reason->getPrevious()->getCode(),
                            $environment
                        ),
                        'json'
                    )
                );
            }
        );

        $event->setResponse($response);
    }

    /**
     * Hide error 5xx messages in prod environment.
     *
     * @param string $message
     * @param int    $code
     * @param string $environment
     *
     * @return array
     */
    protected function resolveExceptionMessage($message, $code, $environment)
    {
        return [
            'error' => 'prod' === $environment && 5 === (int) ($code / 100)
                ? 'Internal server error'
                : $message,
        ];
    }
}
