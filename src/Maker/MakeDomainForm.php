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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Validation;

/**
 * Class MakeDomainForm
 * @package WakeOnWeb\DomainGeneratorBundle\Maker
 */
class MakeDomainForm extends AbstractMaker
{
    use MakeDomainTrait;

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:domain:form';
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
            ->setDescription('Creates form and DTO classes')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                sprintf(
                    'The domain of the form (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->addArgument(
                'form-name',
                InputArgument::REQUIRED,
                sprintf(
                    'The form name (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command generates a form and DTO for a domain

<info>php %command.full_name% Blog Post</info>
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
        $dependencies->addClassDependency(
            AbstractType::class,
            'form'
        );

        $dependencies->addClassDependency(
            Validation::class,
            'validator'
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
        $formName = $input->getArgument('form-name');

        $formClassDetails          = $generator->createClassNameDetails(
            $formName,
            "{$domain}\\Domain\\Form",
            'Type'
        );
        $dtoClassDetails = $generator->createClassNameDetails(
            $formName,
            "{$domain}\\Domain\\Form",
            'DTO'
        );

        $generator->generateClass(
            $formClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/form/FormType.tpl.php",
            [
                'dtoShortName' => $dtoClassDetails->getShortName(),
            ]
        );

        $generator->generateClass(
            $dtoClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/form/DTO.tpl.php",
            []
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }
}
