<?php

namespace WakeOnWeb\DomainGeneratorBundle\Maker;

use Prooph\Bundle\ServiceBus\ProophServiceBusBundle;
use React\Promise\Promise;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class MakeDomainQuery
 * @package WakeOnWeb\DomainGeneratorBundle\Maker
 */
class MakeDomainQuery extends AbstractMaker
{
    use MakeDomainTrait;

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:domain:query';
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
            ->setDescription('Creates domain query and finder')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                sprintf(
                    'The domain of the query (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->addArgument(
                'query-name',
                InputArgument::REQUIRED,
                sprintf(
                    'The query name (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command generates a query and finder for a domain

<info>php %command.full_name% Blog CreatePost</info>
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
        // guarantee ProophServiceBusBundle
        $dependencies->addClassDependency(
            ProophServiceBusBundle::class,
            'prooph/service-bus-symfony-bundle'
        );

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
        $domain      = $input->getArgument('domain');
        $queryName = $input->getArgument('query-name');

        $queryClassDetails        = $generator->createClassNameDetails(
            $queryName,
            "{$domain}\\App\\Query",
            'Query'
        );
        $finderClassDetails = $generator->createClassNameDetails(
            $queryName,
            "{$domain}\\App\\Finder",
            'Finder'
        );

        $generator->generateClass(
            $queryClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/query/Query.tpl.php",
            []
        );
        $generator->generateClass(
            $finderClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/query/Finder.tpl.php",
            [
                'queryShortName' => $queryClassDetails->getShortName(),
                'queryFullName' => $queryClassDetails->getFullName(),
            ]
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }
}
