<?php

namespace WakeOnWeb\DomainGeneratorBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use React\Promise\Promise;

/**
 * Class MakeDomainAction
 * @package WakeOnWeb\DomainGeneratorBundle\Maker
 */
class MakeDomainAction extends AbstractMaker
{
    use MakeDomainTrait;

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:domain:action';
    }

    /**
     * Configure the command: set description, input arguments, options, etc.
     *
     * By default, all arguments will be asked interactively. If you want
     * to avoid that, use the $inputConfig->setArgumentAsNonInteractive() method.
     *
     * @param Command            $command
     * @param InputConfiguration $inputConfig
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates domain class, doctrine schema configuration and repository for class')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                sprintf(
                    'The domain of the action (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->addArgument(
                'action-name',
                InputArgument::REQUIRED,
                sprintf(
                    'The action name (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->addArgument(
                'route-path',
                InputArgument::REQUIRED,
                sprintf(
                    'The route path for the action (e.g. <fg=yellow>%s</>)',
                    Str::asRoutePath(Str::getRandomTerm())
                )
            )
            ->addArgument(
                'http-method',
                InputArgument::REQUIRED,
                sprintf(
                    'The http method for the route (e.g. <fg=yellow>post</>)'
                )
            )
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command generates an action for a domain

<info>php %command.full_name% Blog CreatePost /blogs post</info>
EOF
            )
        ;
    }

    /**
     * Configure any library dependencies that your maker requires.
     *
     * @param DependencyBuilder $dependencies
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        // react promise
        $dependencies->addClassDependency(
            Promise::class,
            'react/promise'
        );
    }

    /**
     * Called after normal code generation: allows you to do anything.
     *
     * @param InputInterface $input
     * @param ConsoleStyle   $io
     * @param Generator      $generator
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $domain     = $input->getArgument('domain');
        $actionName = $input->getArgument('action-name');
        $routePath  = $input->getArgument('route-path');
        $method     = strtoupper($input->getArgument('http-method'));

        $actionClassDetails          = $generator->createClassNameDetails(
            $actionName,
            "{$domain}\\UI\\Actions",
            'Action'
        );
        $promiseListenerClassDetails = $generator->createClassNameDetails(
            'PromiseListener',
            "{$domain}\\UI\\Listener\\View"
        );

        $generator->generateClass(
            $actionClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/action/Action.tpl.php",
            [
                'query'   => in_array($method, ['GET', 'POST']),
                'command' => in_array($method, ['PUT', 'PATCH', 'POST', 'DELETE']),
            ]
        );

        if (!class_exists($promiseListenerClassDetails->getFullName())) {
            $generator->generateClass(
                $promiseListenerClassDetails->getFullName(),
                __DIR__."/../Resources/skeleton/action/PromiseListener.tpl.php",
                []
            );
        }

        $routeName      = Str::asSnakeCase($actionName);
        $fullActionName = $actionClassDetails->getFullName();
        $route          = <<<EOF

# {$actionName}
{$routeName}:
    path: {$routePath}
    methods: [{$method}]
    controller: {$fullActionName}
EOF;
        file_put_contents("$this->projectDir/config/routes.yaml", $route, FILE_APPEND);

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }
}
