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

class MailAccountType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('username');

		if($this->_isEditMode) {
			$builder->add('password', 'repeated', array(
													   'type'     => 'password',
													   'required' => false,
												  ));
		} else {
			$builder->add('password', 'repeated', array(
													   'type'     => 'password',
													   'required' => true,
												  ));
		}

		$builder
			->add('quota')
			->add('hasPop3', null, array('required' => false))
			->add('hasImap4', null, array('required' => false))
			->add('hasSmtp', null, array('required' => false))
			->add('enabled', null, array('required' => false));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
									'data_class' => 'Jeboehm\Lampcp\CoreBundle\Entity\MailAccount'
							   ));
	}

	public function getName() {
		return 'jeboehm_lampcp_corebundle_mailaccounttype';
	}
}