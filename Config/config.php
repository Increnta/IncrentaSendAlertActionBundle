<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Increnta',
    'description' => 'Enable some features like send email alerts on campaigns',
    'version'     => '1.0',
    'author'      => 'Fernando Rubio',
    'services'    => array(
        'events' => array(
            'plugin.increnta.campaignbundle.subscriber' => array(
                'class'     => 'MauticPlugin\IncrentaBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.lead.model.lead',
                    'mautic.email.model.email',
                    'mautic.campaign.model.event',
                    'mautic.channel.model.queue',
                    'mautic.helper.mailer'
                ]
            )
        ),
        'forms'  => array(
            'increnta.campaign.form.sendalert' => array(
                'class' => 'MauticPlugin\IncrentaBundle\Form\Type\SendAlert',
                'arguments' => 'mautic.factory',
                'alias' => 'sendalert'
            )
        )
    )
];
