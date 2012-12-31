<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jboehm\Lampcp\ApacheConfigBundle\Command;

use Jboehm\Lampcp\CoreBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jboehm\Lampcp\ApacheConfigBundle\Service\VhostBuilderService;

class GenerateConfigCommand extends AbstractCommand {
	/** @var VhostBuilderService */
	protected $_vhostBuilderService;

	/**
	 * @return \Jboehm\Lampcp\ApacheConfigBundle\Service\VhostBuilderService
	 */
	protected function _getVhostBuilderService() {
		if(!$this->_vhostBuilderService) {
			$this->_vhostBuilderService = $this->getContainer()->get('jboehm_lampcp_apache_config_vhostbuilder');
		}

		return $this->_vhostBuilderService;
	}

	/**
	 * Configure command
	 */
	protected function configure() {
		$this->setName('lampcp:apache:generateconfig');
		$this->setDescription('Generates the apache2 configuration');
	}

	/**
	 * Execute command
	 *
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$vhost = $this->_getVhostBuilderService();
		$vhost->writeConfigFiles();
	}
}
