<?php
/**
 * This file is part of Jarves.
 *
 * (c) Marc J. Schmidt <marc@marcjschmidt.de>
 *
 *     J.A.R.V.E.S - Just A Rather Very Easy [content management] System.
 *
 *     http://jarves.io
 *
 * To get the full copyright and license information, please view the
 * LICENSE file, that was distributed with this source code.
 */

namespace Jarves\Command;

use Jarves\PackageManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class DemoDataCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('jarves:install:demo')
            ->setDescription('Installs demo data.')
            ->addArgument('hostname', null, 'The hostname of the domain we should add. Example: 127.0.0.1')
            ->addArgument('path', null, 'The path of the domain we should add. Example: /jarves-1.0/ or just /')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jarves = $this->getJarves();

        $mainPackageManager = 'Jarves\PackageManager';

        /** @var PackageManager $packageManager */
        $packageManager = new $mainPackageManager();
        $packageManager->setDomain($input->getArgument('hostname'));
        $packageManager->setPath($input->getArgument('path'));
        $packageManager->setContainer($this->getContainer());
        $packageManager->installDemoData();

        foreach ($jarves->getBundles() as $bundle) {
            $class = $bundle->getNamespace() . '\\PackageManager';
            if ($class !== $mainPackageManager && class_exists($class)) {
                $packageManager = new $class;
                if ($packageManager instanceof ContainerAwareInterface) {
                    $packageManager->setContainer($this->getContainer());
                }

                if (method_exists($packageManager, 'installDemoData')) {
                    $packageManager->installDemoData($jarves);
                }
            }
        }

        $this->getContainer()->get('jarves.cache.cacher')->invalidateCache('/');
    }
}
