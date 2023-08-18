<?php
/**
 * Created by PhpStorm.
 * User: simongroenewolt
 * Date: 06/02/2019
 * Time: 18:08
 */

namespace App\Tests;

use App\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TimingTestsKernel extends Kernel
{
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        echo "hello hello hello";
        parent::configureContainer($container, $loader);
        $loader->load($this->getProjectDir() . '/tests/config_fake_time.yml');
    }



}