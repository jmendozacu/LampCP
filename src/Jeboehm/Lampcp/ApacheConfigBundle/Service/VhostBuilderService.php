<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jeboehm\Lampcp\ApacheConfigBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

use Jeboehm\Lampcp\CoreBundle\Entity\Domain;
use Jeboehm\Lampcp\CoreBundle\Entity\IpAddress;
use Jeboehm\Lampcp\ApacheConfigBundle\IBuilder\BuilderServiceInterface;
use Jeboehm\Lampcp\ApacheConfigBundle\Model\Vhost;
use Jeboehm\Lampcp\ApacheConfigBundle\Exception\CouldNotWriteFileException;

class VhostBuilderService extends AbstractBuilderService implements BuilderServiceInterface {
	const _twigVhost       = 'JeboehmLampcpApacheConfigBundle:Apache2:vhost.conf.twig';
	const _twigFcgiStarter = 'JeboehmLampcpApacheConfigBundle:PHP:php-fcgi-starter.sh.twig';
	const _twigPhpIni      = 'JeboehmLampcpApacheConfigBundle:PHP:php.ini.twig';
	const _domainFileName  = '20_vhost.conf';

	/**
	 * Render php.ini
	 *
	 * @param \Jeboehm\Lampcp\CoreBundle\Entity\Domain $domain
	 *
	 * @throws \Exception
	 * @return string
	 */
	protected function _renderPhpIni(Domain $domain) {
		$phpIniPath   = $this
			->_getConfigService()
			->getParameter('apache.pathphpini');
		$globalConfig = null;

		if(is_readable($phpIniPath)) {
			$globalConfig = file_get_contents($phpIniPath);
		}

		if(empty($globalConfig)) {
			throw new \Exception('Could not read global php.ini');
		}

		return $this->_renderTemplate(self::_twigPhpIni, array(
															  'domain' => $domain,
															  'global' => $globalConfig,
														 ));
	}

	/**
	 * Generate and save FCGI Starter Script
	 *
	 * @param \Jeboehm\Lampcp\CoreBundle\Entity\Domain $domain
	 *
	 * @throws \Jeboehm\Lampcp\ApacheConfigBundle\Exception\CouldNotWriteFileException
	 * @return void
	 */
	protected function _generateFcgiStarterForDomain(Domain $domain) {
		$fs       = new Filesystem();
		$filename = $domain->getPath() . '/php-fcgi/php-fcgi-starter.sh';
		$content  = $this->_renderTemplate(self::_twigFcgiStarter, array(
																		'domain' => $domain,
																   ));

		if(!is_writable(dirname($filename))) {
			throw new CouldNotWriteFileException();
		}

		$this->_getLogger()->info('(VhostBuilderService) Generating FCGI-Starter: ' . $filename);
		file_put_contents($filename, $content);

		// Change rights
		$fs->chmod($filename, 0755);

		// Change user & group
		$fs->chown($filename, $domain->getUser()->getName());
		$fs->chgrp($filename, $domain->getUser()->getGroupname());
	}

	/**
	 * Generate and save php.ini
	 *
	 * @param \Jeboehm\Lampcp\CoreBundle\Entity\Domain $domain
	 *
	 * @throws \Jeboehm\Lampcp\ApacheConfigBundle\Exception\CouldNotWriteFileException
	 */
	protected function _generatePhpIniForDomain(Domain $domain) {
		$fs       = new Filesystem();
		$filename = $domain->getPath() . '/conf/php.ini';

		if(!is_writable(dirname($filename))) {
			throw new CouldNotWriteFileException();
		}

		if(!file_exists($filename)) {
			$this->_getLogger()->info('(VhostBuilderService) Generating php.ini:' . $filename);
			file_put_contents($filename, $this->_renderPhpIni($domain));
		}

		// Change rights
		$fs->chmod($filename, 0440);

		// Change user & group
		$fs->chown($filename, $domain->getUser()->getName());
		$fs->chgrp($filename, $domain->getUser()->getGroupname());
	}

	/**
	 * Save vhost config
	 *
	 * @param string $content
	 *
	 * @throws \Jeboehm\Lampcp\ApacheConfigBundle\Exception\CouldNotWriteFileException
	 * @return void
	 */
	protected function _saveVhostConfig($content) {
		$target = $this
			->_getConfigService()
			->getParameter('apache.pathapache2conf') . '/' . self::_domainFileName;

		$content = str_replace('  ', '', $content);
		$content = str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $content);

		if(!is_writable(dirname($target))) {
			throw new CouldNotWriteFileException();
		}

		$this->_getLogger()->info('(VhostBuilderService) Creating new config: ' . $target);
		file_put_contents($target, $content);
	}

	/**
	 * Get all IPs
	 *
	 * @return \Jeboehm\Lampcp\CoreBundle\Entity\IpAddress[]
	 */
	protected function _getAllIpAddresses() {
		/** @var $ips IpAddress[] */
		$ips = $this
			->_getDoctrine()
			->getRepository('JeboehmLampcpCoreBundle:IpAddress')
			->findAll();

		return $ips;
	}

	/**
	 * Build all configurations
	 */
	public function buildAll() {
		/** @var $models Vhost[] */
		$models = array();

		foreach($this->_getAllDomains() as $domain) {
			if($domain->getIpaddress()->count() > 0) {
				foreach($domain->getIpaddress() as $ipaddress) {
					/** @var $ipaddress IpAddress */
					$vhost = new Vhost();
					$vhost
						->setDomain($domain)
						->setIpaddress($ipaddress);
					$models[] = $vhost;
				}
			} else {
				$vhost = new Vhost();
				$vhost->setDomain($domain);
				$models[] = $vhost;
			}

			$this->_generatePhpIniForDomain($domain);
			$this->_generateFcgiStarterForDomain($domain);
		}

		foreach($this->_getAllSubdomains() as $subdomain) {
			if($subdomain->getDomain()->getIpaddress()->count() > 0) {
				foreach($subdomain->getDomain()->getIpaddress() as $ipaddress) {
					/** @var $ipaddress IpAddress */
					$vhost = new Vhost();
					$vhost
						->setDomain($domain)
						->setSubdomain($subdomain)
						->setIpaddress($ipaddress);
					$models[] = $vhost;
				}
			} else {
				$vhost = new Vhost();
				$vhost
					->setDomain($domain)
					->setSubdomain($subdomain);
				$models[] = $vhost;
			}
		}

		$content = $this->_renderTemplate(self::_twigVhost, array(
																 'vhosts' => $models,
																 'ips'    => $this->_getAllIpAddresses(),
															));

		$this->_saveVhostConfig($content);
	}
}
