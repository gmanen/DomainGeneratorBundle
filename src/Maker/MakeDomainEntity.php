<?php

namespace WakeOnWeb\DomainGeneratorBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Mapping\Column;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;

/**
 * Class MakeDomainEntity
 * @package WakeOnWeb\DomainGeneratorBundle\Maker
 */
class MakeDomainEntity extends AbstractMaker
{
    use MakeDomainTrait;

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:domain:entity';
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
                    'The domain of the entity (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->addArgument(
                'entity-class',
                InputArgument::REQUIRED,
                sprintf(
                    'The class name of the entity (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command generates an entity for a domain with Repository, RepositoryInterface, doctrine xml mapping

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
        // guarantee DoctrineBundle
        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm'
        );

        // guarantee ORM
        $dependencies->addClassDependency(
            Column::class,
            'orm'
        );

        // serializer
        $dependencies->addClassDependency(
            Serializer::class,
            'serializer'
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
        $entityClass = $input->getArgument('entity-class');

        $entityClassDetails         = $generator->createClassNameDetails(
            $entityClass,
            "{$domain}\\Domain\\"
        );
        $normalizerClassDetails     = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix(),
            "{$domain}\\Infra\\Serializer\\Normalizer",
            'Normalizer'
        );
        $repositoryClassDetails     = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix(),
            "{$domain}\\Infra\\Repository\\DoctrineORM",
            'Repository'
        );
        $repositoryInterfaceDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix(),
            "{$domain}\\Domain\\Repository",
            'RepositoryInterface'
        );
        $abstractRepositoryDetails  = $generator->createClassNameDetails(
            "Abstract",
            "{$domain}\\Infra\\Repository\\DoctrineORM",
            'Repository'
        );

        $generator->generateClass(
            $entityClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/domain/Entity.tpl.php",
            []
        );

        $generator->generateClass(
            $normalizerClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/domain/Normalizer.tpl.php",
            [
                'entityClassName' => $entityClassDetails->getFullName(),
                'entityShortName' => $entityClassDetails->getShortName(),
            ]
        );

        $generator->generateClass(
            $repositoryClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/domain/Repository.tpl.php",
            [
                'interfaceShortName' => $repositoryInterfaceDetails->getShortName(),
                'interfaceFullName'  => $repositoryInterfaceDetails->getFullName(),
                'abstractShortName'  => $abstractRepositoryDetails->getShortName(),
            ]
        );

        $generator->generateClass(
            $repositoryInterfaceDetails->getFullName(),
            __DIR__."/../Resources/skeleton/domain/RepositoryInterface.tpl.php",
            []
        );

        if (!class_exists($abstractRepositoryDetails->getFullName())) {
            $generator->generateClass(
                $abstractRepositoryDetails->getFullName(),
                __DIR__."/../Resources/skeleton/domain/AbstractRepository.tpl.php",
                []
            );
        }

        $generator->generateClass(
            $entityClassDetails->getFullName(),
            __DIR__."/../Resources/skeleton/domain/Entity.tpl.php",
            []
        );

        $generator->generateFile(
            "{$this->projectDir}/src/{$domain}/Infra/Resources/config/doctrine-mapping/{$entityClass}.orm.xml",
            __DIR__."/../Resources/skeleton/domain/schema.tpl.php",
            [
                'fullClassName'   => $entityClassDetails->getFullName(),
                'underscoredName' => Str::asSnakeCase($entityClassDetails->getShortName()),
            ]
        );

        // Adds doctrine mapping configuration for the domain if it doesn't exist yet
        $doctrineMapping = Yaml::parseFile($doctrineMappingFile = "{$this->projectDir}/config/packages/doctrine.yaml");

        if (!array_key_exists('mappings', $doctrineMapping['doctrine']['orm']) || ! $doctrineMapping['doctrine']['orm']['mappings'] || !array_key_exists($domain, $doctrineMapping['doctrine']['orm']['mappings'])) {
            $doctrineMapping['doctrine']['orm']['mappings'][$domain] = [
                'is_bundle' => false,
                'type' => 'xml',
                'dir' => "%kernel.project_dir%/src/{$domain}/Infra/Resources/config/doctrine-mapping",
                'prefix' => "App\\{$domain}\\Domain",
            ];

            file_put_contents($doctrineMappingFile, Yaml::dump($doctrineMapping, 5));
        }

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }
}
