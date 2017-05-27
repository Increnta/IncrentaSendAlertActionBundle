<?php

namespace MauticPlugin\IncrentaBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CampaignBundle\Event as Events;
use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Model\EventModel;
use Mautic\ChannelBundle\Model\MessageQueueModel;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailOpenEvent;
use Mautic\EmailBundle\Helper\MailHelper;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\EmailBundle\Entity\Email;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
/**
 * Class LeadSubscriber
 *
 * @package Mautic\CampaignBundle\EventListeners
 */
class CampaignSubscriber extends CommonSubscriber
{

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var EmailModel
     */
    protected $emailModel;

    /**
     * @var EmailModel
     */
    protected $messageQueueModel;

    /**
     * @var EventModel
     */
    protected $campaignEventModel;


    /**
     * @var MailHelper
     */
    protected $mailer;
    /**
     * CampaignSubscriber constructor.
     *
     * @param LeadModel         $leadModel
     * @param EmailModel        $emailModel
     * @param EventModel        $eventModel
     * @param MailHelper        $mailer
     * @param MessageQueueModel $messageQueueModel
     */
    public function __construct(LeadModel $leadModel, EmailModel $emailModel, EventModel $eventModel, MessageQueueModel $messageQueueModel, MailHelper $mailer)
    {
        $this->leadModel          = $leadModel;
        $this->emailModel         = $emailModel;
        $this->campaignEventModel = $eventModel;
        $this->messageQueueModel  = $messageQueueModel;
        $this->mailer             = $mailer->getMailer();
    }

    /**
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            CampaignEvents::CAMPAIGN_ON_BUILD => array('onCampaignBuild', 0),
            'executeSendAlertAction' => array('executeSendAlert', 0)
        );
    }

    /**
     * @param array $properties
     * @return string
     */
    public function generatePropertiesPlainText($properties = array())
    {
        $propertiesPlain = "";
        foreach($properties as $key => $value)
            $propertiesPlain .= "$key : $value<br />";

        return $propertiesPlain;
    }


    /**
     * @param Email $email
     * @param array $leadProperties
     * @return string
     */
    public function replaceTokensContent(Email $email, $leadProperties=array())
    {
        $content = $email->getCustomHtml();

        foreach($leadProperties as $property => $value){
            if($value){
                $replaceValue = ($value) ? $value : "";
                $content = str_replace('{leadfield='.$property.'}', $replaceValue, $content);
                $content = str_replace('{contactfield='.$property.'}', $replaceValue, $content);
            }
        }

        return $content;
    }

    /**
     * @param CampaignExecutionEvent $event
     * @return $this
     */
    public function executeSendAlert(CampaignExecutionEvent $event)
    {
        $lead          = $event->getLead();
        $leadProperties = $lead->getProfileFields();
        $config        = $event->getConfig();
        $sendTo        = explode(",", $config["sendto"]);
        $email         = $this->emailModel->getEntity($config["email"]);

        $this->mailer->setTo($sendTo);
        $email->setCustomHtml($this->replaceTokensContent($email, $leadProperties));
        $this->mailer->setEmail($email);

//        $this->mailer->setSubject(
//          $this->translator->trans("plugin.increnta.campaign.mailer.sendto.subject")
//        );
//
//        $plainLeadProperties = $this->generatePropertiesPlainText($leadProperties);
//        $this->mailer->setBody("<h1>Nuevo contacto</h1><p>Las propiedades del contacto son:</p><br /> $plainLeadProperties");
//        $this->mailer->parsePlainText();
        $send = $this->mailer->send();
        error_log("el resutlado del envío es");
        error_log(json_encode(array("send result"=>$send)));
        $event->setResult($send);
    }


    /**
     * Add campaign decision and actions
     *
     * @param Events\CampaignBuilderEvent $event
     */
    public function onCampaignBuild(Events\CampaignBuilderEvent $event)
    {

        //Add action to actually add/remove lead to a specific lists
        $sendAlertAction = [
            'label'           => 'increnta.campaign.event.sendalert',
            'description'     => 'increnta.campaign.event.sendalert_descr',
            'formType'        => 'sendalert',
            'formTypeOptions' => [
            ],
            'eventName' => 'executeSendAlertAction',
        ];
        $event->addAction('increnta.campaign.sendalert', $sendAlertAction);

    }


}