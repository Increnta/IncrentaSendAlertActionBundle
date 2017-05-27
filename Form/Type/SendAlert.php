<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\IncrentaBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Mautic\CoreBundle\Form\DataTransformer\EmojiToShortTransformer;
use Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Mautic\CoreBundle\Form\Type\DynamicContentTrait;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;


/**
 * Class CampaignType.
 */
class SendAlert extends AbstractType
{

    use DynamicContentTrait;

    private $translator;
    private $defaultTheme;
    private $em;
    private $request;

    private $countryChoices  = [];
    private $regionChoices   = [];
    private $timezoneChoices = [];
    private $stageChoices    = [];
    private $localeChoices   = [];

    /**
     * SendAlert constructor.
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->translator   = $factory->getTranslator();
        $this->defaultTheme = $factory->getParameter('theme');
        $this->em           = $factory->getEntityManager();
        $this->request      = $factory->getRequest();

        $this->countryChoices  = FormFieldHelper::getCountryChoices();
        $this->regionChoices   = FormFieldHelper::getRegionChoices();
        $this->timezoneChoices = FormFieldHelper::getTimezonesChoices();
        $this->localeChoices   = FormFieldHelper::getLocaleChoices();

        $stages = $factory->getModel('stage')->getRepository()->getSimpleList();

        foreach ($stages as $stage) {
            $this->stageChoices[$stage['value']] = $stage['label'];
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('sendto', 'text', [
            'label'      => 'plugin.increnta.form.field.sendto',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
        ]);

        $builder->add(
            'email',
            'email_list',
            [
                'label'      => 'mautic.email.send.selectemails',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mautic.email.choose.emails_descr',
                    'onchange' => 'Mautic.disabledEmailAction(window, this)',
                ],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.email.chooseemail.notblank']
                    ),
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sendalert';
    }
}
