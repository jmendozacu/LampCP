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
use Symfony\Component\Console\Input\InputOption;
use Jboehm\Lampcp\ApacheConfigBundle\Service\VhostBuilderService;
use Jboehm\Lampcp\ApacheConfigBundle\Service\DirectoryBuilderService;
use Jboehm\Lampcp\ApacheConfigBundle\Service\ProtectionBuilderService;
use Jboehm\Lampcp\ApacheConfigBundle\Service\PathOptionBuilderService;
use Jboehm\Lampcp\CoreBundle\Entity\BuilderChangeRepository;
use Jboehm\Lampcp\CoreBundle\Utilities\ExecUtility;

class GenerateConfigCommand extends AbstractCommand {
	/**
	 * Get watched entitys
	 *
	 * @return array
	 */
	protected function _getEntitys() {
		$entitys = array(
			'Jboehm\Lampcp\CoreBundle\Entity\Domain',
			'Jboehm\Lampcp\CoreBundle\Entity\Subdomain',
			'Jboehm\Lampcp\CoreBundle\Entity\PathOption',
			'Jboehm\Lampcp\CoreBundle\Entity\Protection',
			'Jboehm\Lampcp\CoreBundle\Entity\ProtectionUser',
			'Jboehm\Lampcp\CoreBundle\Entity\IpAddress',
		);

		return $entitys;
	}

	/**
	 * @return VhostBuilderService
	 */
	protected function _getVhostBuilderService() {
		return $this->getContainer()->get('jboehm_lampcp_apache_config_vhostbuilder');
	}

	/**
	 * @return DirectoryBuilderService
	 */
	protected function _getDirectoryBuilderService() {
		return $this->getContainer()->get('jboehm_lampcp_apache_config_directorybuilder');
	}

	/**
	 * @return ProtectionBuilderService
	 */
	protected function _getProtectionBuilderService() {
		return $this->getContainer()->get('jboehm_lampcp_apache_config_protectionbuilder');
	}

	/**
	 * @return PathOptionBuilderService
	 */
	protected function _getPathOptionBuilderService() {
		return $this->getContainer()->get('jboehm_lampcp_apache_config_pathoptionbuilder');
	}

	/**
	 * Configure command
	 */
	protected function configure() {
		$this->setName('lampcp:apache:generateconfig');
		$this->setDescription('Generates the apache2 configuration');
		$this->addOption('force', InputOption::VALUE_OPTIONAL);
	}

	/**
	 * Execute command
	 *
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 *
	 * @throws \Exception
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$run = false;

		if($input->getOption('force') || $this->_isChanged()) {
			$run = true;
		}

		if($run) {
			$this->_getLogger()->info('(GenerateConfigCommand) Executing...');

			if($input->getOption('verbose')) {
				$output->writeln('(GenerateConfigCommand) Executing...');
			}

			try {
				$directory = $this->_getDirectoryBuilderService();
				$directory->createDirectorysForAllDomains();

				$vhost = $this->_getVhostBuilderService();
				$vhost->buildAll();
				$vhost->cleanVhostDirectory();

				$protection = $this->_getProtectionBuilderService();
				$protection->buildAll();
				$protection->cleanConfDirectory();

				$pathoption = $this->_getPathOptionBuilderService();
				$pathoption->buildAll();

				$this->_restartApache();
			} catch(\Exception $e) {
				$this->_getLogger()->err('(GenerateConfigCommand) Error: ' . $e->getMessage());

				throw $e;
			}
		} else {
			if($input->getOption('verbose')) {
				$output->writeln('(GenerateConfigCommand) No changes detected.');
			}
		}
	}

	/**
	 * Checks for changed entitys that are relevant for this task
	 *
	 * @return bool
	 */
	protected function _isChanged() {
		/** @var $repo BuilderChangeRepository */
		$repo = $this->_getDoctrine()->getRepository('JboehmLampcpCoreBundle:BuilderChange');
		$data = $repo->getByEntitynamesArray($this->_getEntitys());

		if(count($data) > 0) {
			foreach($data as $entity) {
				$this->_getDoctrine()->remove($entity);
			}

			$this->_getDoctrine()->flush();

			return true;
		}

		return false;
	}

	/**
	 * Restart apache2
	 */
	protected function _restartApache() {
		$exec = new ExecUtility();
		$cmd  = $this
			->_getConfigService()
			->getParameter('apache.cmdapache2restart');

		if(!empty($cmd)) {
			$this->_getLogger()->info('(GenerateConfigCommand) Restarting apache2...');

			if(strpos($cmd, ' ') !== false) {
				$cmdSplit = explode(' ', $cmd);
				$exec->exec(array_shift($cmdSplit), $cmdSplit);
			} else {
				$exec->exec($cmd);
			}

			if($exec->getCode() > 0) {
				$this->_getLogger()->err($exec->getOutput());
			}
		}
	}
}
