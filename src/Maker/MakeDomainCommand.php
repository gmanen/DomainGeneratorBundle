<?php

namespace WakeOnWeb\DomainGeneratorBundle\Maker;

use Prooph\Bundle\ServiceBus\ProophServiceBusBundle;
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
 * Class MakeDomainCommand
 * @package WakeOnWeb\DomainGeneratorBundle\Maker
 */
class MakeDomainCommand extends AbstractMaker
{
    use MakeDomainTrait;

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:domain:command';
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
            ->setDescription('Creates domain command and command handler')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                sprintf(
                    'The domain of the command (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->addArgument(
                'command-name',
                InputArgument::REQUIRED,
                sprintf(
                    'The command name (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command generates a command and command handler for a domain

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
        $commandName = $input->getArgument('command-name');

        $commandClassDetails        = $generator->createClassNameDetails(
            $commandName,
            "{$domain}\\App\\Command",
            'Command'
        );
        $commandHandlerClassDetails = $generator->createClassNameDetails(
            $commandName,
            "{$domain}\\App\\CommandHandler",
            'CommandHandler'
        );

        $generator->generateClass(
            $commandClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/command/Command.tpl.php",
            []
        );
        $generator->generateClass(
            $commandHandlerClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/command/CommandHandler.tpl.php",
            [
                'commandShortName' => $commandClassDetails->getShortName(),
                'commandFullName'  => $commandClassDetails->getFullName(),
            ]
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }
}
