<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\InventoryBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Inventory extension.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 * @author Saša Stamenković <umpirsky@gmail.com>
 */
class SyliusInventoryExtension extends AbstractResourceExtension
{
    protected $configFiles = array(
        'services.xml',
        'templating.xml',
        'twig.xml',
    );

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->configure(
            $config,
            new Configuration(),
            $container,
            self::CONFIGURE_LOADER | self::CONFIGURE_DATABASE | self::CONFIGURE_PARAMETERS | self::CONFIGURE_VALIDATORS
        );

        $container->setParameter('sylius.backorders', $config['backorders']);

        $container->setAlias('sylius.availability_checker', $config['checker']);
        $container->setAlias('sylius.inventory_operator', $config['operator']);

        $classes = $config['classes'];

        $container->setParameter('sylius.controller.inventory_unit.class', $classes['inventory_unit']['controller']);
        $container->setParameter('sylius.model.inventory_unit.class', $classes['inventory_unit']['model']);

        if (array_key_exists('repository', $classes['inventory_unit'])) {
            $container->setParameter(
                'sylius.repository.inventory_unit.class',
                $classes['inventory_unit']['repository']
            );
        }

        $container->setParameter('sylius.model.stockable.class', $classes['stockable']['model']);

        if (isset($config['events'])) {
            $listenerDefinition = $container->getDefinition('sylius.listener.inventory');
            foreach ($config['events'] as $event) {
                $listenerDefinition->addTag(
                    'kernel.event_listener',
                    array('event' => $event, 'method' => 'onInventoryChange')
                );
            }
        }
    }
}
