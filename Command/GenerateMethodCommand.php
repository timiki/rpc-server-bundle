<?php

namespace Timiki\Bundle\RpcServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class GenerateMethodCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:method')
            ->setDescription('Generates a new RPC method')
            ->addArgument('name', InputArgument::OPTIONAL, 'The method name')
            ->setHelp(
                <<<EOT
The <info>generate:method</info> command helps you generate new RPC method
inside bundles. Provide the bundle name as the first argument and the method
name as the second argument:

<info>php bin/console generate:method</info>
EOT
            );
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $io        = new SymfonyStyle($input, $output);
        $bundle    = $input->getArgument('bundle');
        $name      = $input->getArgument('name');
        $container = $this->getContainer();

        if (null !== $bundle && null !== $name) {
            return;
        }

        $io->title('Generate new RPC method');

        // Method name

        $name = $io->ask(
            'Method name',
            null,
            function ($answer) use ($container, $bundle) {

                if (empty($answer)) {
                    throw new \RuntimeException('Method name can`t be empty.');
                }

                $answer = str_replace(' ', ':', $answer);

                if ($this->isMethodExist($container->get('kernel')->getBundle($bundle), $answer)) {
                    throw new \RuntimeException(
                        sprintf(
                            'Method "%s" already exist.',
                            $answer
                        )
                    );
                }

                return $answer;
            }
        );

        $input->setArgument('name', $name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io        = new SymfonyStyle($input, $output);
        $name      = str_replace(' ', ':', $input->getArgument('name'));

        if ($this->isMethodExist($name)) {
            throw new \RuntimeException(
                sprintf(
                    'Method "%s" already exist.',
                    $name
                )
            );
        }

        $methodClassName = $this->classify($name);
        $methodFile      = $this->getMethodPath($name);

        if (file_exists($methodFile)) {
            throw new \RuntimeException(
                sprintf(
                    'Method "%s" already exists',
                    $name
                )
            );
        }

        $parameters = [
            'class'     => $methodClassName,
            'name'      => $name,
        ];

        $this->renderFile('Method.php.twig', $methodFile, $parameters);

        $io->success(
            sprintf(
                'Method "%s" was generate in file "%s".',
                $name,
                $methodFile
            )
        );
    }

    /**
     * @param string $name
     * @return boolean
     */
    protected function isMethodExist($name)
    {
        return file_exists($this->getMethodPath($name));
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getMethodPath($name)
    {
        $methodDir = $this->getContainer()->getParameter('kernel.root_dir').'/Method';

        return $methodDir.'/'.$this->classify($name).'.php';
    }

    /**
     * @param string $string
     * @return string
     */
    protected function classify($string)
    {
        return str_replace(' ', '', ucwords(strtr($string, '_-:', '   '))).'Method';
    }


    /**
     * @param $template
     * @param $parameters
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render($template, $parameters)
    {
        $twig = $this->getTwigEnvironment();

        return $twig->render($template, $parameters);
    }

    /**
     * @return \Twig_Environment
     */
    protected function getTwigEnvironment()
    {
        return new \Twig_Environment(
            new \Twig_Loader_Filesystem(
                [
                    dirname(__DIR__).'/Resources/skeleton',
                ]
            ), [
                'debug'            => true,
                'cache'            => false,
                'strict_variables' => true,
                'autoescape'       => false,
            ]
        );
    }

    /**
     * @param $template
     * @param $target
     * @param $parameters
     * @return int
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function renderFile($template, $target, $parameters)
    {
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        return file_put_contents($target, $this->render($template, $parameters));
    }
}
