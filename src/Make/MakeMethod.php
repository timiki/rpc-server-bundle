<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Make;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeMethod extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:method';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new JSON-RPC method';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription(self::getCommandDescription())
            ->addArgument('name', InputArgument::OPTIONAL, \sprintf('Choose a method name (e.g. <fg=yellow>%s</>)', Str::getRandomTerm()))
            ->setHelp(
                <<<EOT
The <info>make:method</info> command helps you make new RPC Method.

<info>php bin/console make:method</info>
EOT
            );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $name = \trim($input->getArgument('name'));

        $classNameDetails = $generator->createClassNameDetails(
            $name,
            'Method\\',
            'Method'
        );

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/Method.tpl.php',
            [
                'method_name' => \mb_strtolower(\str_replace('\\', '.', $name)),
            ]
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);

        $io->text(
            [
                'Next: open your new method class and customize it!',
                'Find the documentation at <fg=yellow>https://github.com/timiki/rpc-server-bundle</>',
            ]
        );
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies->addClassDependency(Annotation::class, 'doctrine/annotations');
    }
}
