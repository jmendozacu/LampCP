<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jeboehm\Lampcp\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SubdomainType
 *
 * Builds a subdomain form
 *
 * @package Jeboehm\Lampcp\CoreBundle\Form\Type
 * @author  Jeffrey Böhm <post@jeffrey-boehm.de>
 */
class SubdomainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $domain \Jeboehm\Lampcp\CoreBundle\Entity\Domain */
        $domain = $builder
            ->getData()
            ->getDomain();

        $builder
            ->add(
                'subdomain',
                null,
                array(
                     'attr' => array(
                         'append_input' => '.' . $domain->getDomain()
                     )
                )
            )
            ->add(
                'parent',
                'entity',
                array(
                     'class'    => 'JeboehmLampcpCoreBundle:Subdomain',
                     'property' => 'fullDomain',
                     'required' => false,
                )
            )
            ->add('path', null, array('required' => false))
            ->add(
                'redirectUrl',
                null,
                array(
                     'required' => false,
                     'attr'     => array(
                         'help_block' => 'jeboehm.lampcp.corebundle.subdomaintype.redirectUrl.help',
                     )
                )
            )
            ->add(
                'certificate',
                'entity',
                array(
                     'class'    => 'JeboehmLampcpCoreBundle:Certificate',
                     'property' => 'name',
                     'required' => false,
                )
            )
            ->add('forceSsl', null, array('required' => false))
            ->add('isWildcard', null, array('required' => false))
            ->add('parsePhp', null, array('required' => false))
            ->add('customconfig', null, array('required' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'data_class' => 'Jeboehm\Lampcp\CoreBundle\Entity\Subdomain'
            )
        );
    }

    public function getName()
    {
        return 'jeboehm_lampcp_corebundle_subdomaintype';
    }
}
