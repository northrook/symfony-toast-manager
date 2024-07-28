<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Service;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @author  Martin Nielsen <mn@northrook.com>
 */
class SymfonyToastsBundle extends AbstractBundle
{

    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {
        $services = $container->services();


        $services->set( ToastService::class )
                 ->args( [ service( 'request_stack' ) ] )
                 ->autowire( true );
    }

    public function getPath() : string {
        return dirname( __DIR__ );
    }
}