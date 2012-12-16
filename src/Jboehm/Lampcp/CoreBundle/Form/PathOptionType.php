<?php

namespace Jboehm\Lampcp\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PathOptionType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('path')
			->add('hasDirectoryListing', null, array('required' => false))
			->add('error404', null, array('required' => false))
			->add('error403', null, array('required' => false))
			->add('error500', null, array('required' => false));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
									'data_class' => 'Jboehm\Lampcp\CoreBundle\Entity\PathOption'
							   ));
	}

	public function getName() {
		return 'jboehm_lampcp_corebundle_pathoptiontype';
	}
}
