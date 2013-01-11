<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jeboehm\Lampcp\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DomainType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		if($this->_getIsEditMode()) {
			$builder->add('domain', null, array(
											   'read_only' => true
										  ));
		} else {
			$builder->add('domain', null, array(
											   'read_only' => false
										  ));
		}


		$builder
			->add('path', null, array('read_only' => true))
			->add('webroot')
			->add('user', 'entity', array(
										 'class'    => 'JeboehmLampcpCoreBundle:User',
										 'property' => 'name',
									))
			->add('ipaddress', 'entity', array(
											  'class'    => 'JeboehmLampcpCoreBundle:IpAddress',
											  'property' => 'alias',
											  'multiple' => true,
											  'required' => false,
										 ))
			->add('customconfig', null, array('required' => false));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
									'data_class' => 'Jeboehm\Lampcp\CoreBundle\Entity\Domain'
							   ));
	}

	public function getName() {
		return 'jeboehm_lampcp_corebundle_domaintype';
	}
}