<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jboehm\Lampcp\ApacheConfigBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Jboehm\Lampcp\ApacheConfigBundle\Exception\CouldNotWriteFileException;
use Jboehm\Lampcp\ApacheConfigBundle\Model\Protection as ProtectionConfigModel;
use Jboehm\Lampcp\CoreBundle\Entity\Protection as ProtectionEntity;
use Jboehm\Lampcp\CoreBundle\Entity\Domain;

class ProtectionBuilderService extends AbstractBuilderService {
	const _twigAuthUserFile = 'JboehmLampcpApacheConfigBundle:Default:AuthUserFile.conf.twig';

	/**
	 * Get protection model array
	 *
	 * @param \Jboehm\Lampcp\CoreBundle\Entity\Protection $protection
	 *
	 * @return \Jboehm\Lampcp\ApacheConfigBundle\Model\Protection[]
	 */
	protected function _getProtectionModelArray(ProtectionEntity $protection) {
		$models = array();

		foreach($protection->getProtectionuser() as $prot) {
			$mod = new ProtectionConfigModel();
			$mod
				->setUsername($prot->getUsername())
				->setPassword($this->_getCryptService()->decrypt($prot->getPassword()));

			$models[] = $mod;
		}

		return $models;
	}

	/**
	 * Render AuthUserFile
	 *
	 * @param array $models
	 *
	 * @return string
	 */
	protected function _renderAuthUserFile(array $models) {
		return $this->_getTemplating()->render(self::_twigAuthUserFile, array('users' => $models));
	}

	/**
	 * Generate AuthUserFile
	 *
	 * @param \Jboehm\Lampcp\CoreBundle\Entity\Protection $protection
	 *
	 * @throws \Jboehm\Lampcp\ApacheConfigBundle\Exception\CouldNotWriteFileException
	 */
	protected function _generateAuthUserFile(ProtectionEntity $protection) {
		$fs               = new Filesystem();
		$pathAuthUserFile = sprintf(
			'%s/conf/authuser_%s.passwd',
			$protection->getDomain()->getPath(),
			$protection->getId()
		);

		if(!is_writable(dirname($pathAuthUserFile))) {
			throw new CouldNotWriteFileException();
		}

		$this->_getLogger()->info('(ProtectionBuilderService) Generating AuthUserFile:' . $pathAuthUserFile);
		file_put_contents($pathAuthUserFile, $this->_renderAuthUserFile($this->_getProtectionModelArray($protection)));

		// Change rights
		$fs->chmod($pathAuthUserFile, 0440);

		// Change user & group
		$fs->chown($pathAuthUserFile, $protection->getDomain()->getUser()->getName());
		$fs->chgrp($pathAuthUserFile, $protection->getDomain()->getUser()->getGroupname());
	}

	/**
	 * Build domain's protections configuration
	 *
	 * @param \Jboehm\Lampcp\CoreBundle\Entity\Domain $domain
	 *
	 * @return void
	 */
	public function buildDomain(Domain $domain) {
		foreach($domain->getProtection() as $protection) {
			$this->_generateAuthUserFile($protection);
		}
	}

	/**
	 * Build all configurations
	 */
	public function buildAll() {
		foreach($this->_getAllDomains() as $domain) {
			$this->buildDomain($domain);
		}
	}

	/**
	 * Look for obsolete AuthUserFile files
	 */
	public function cleanConfDirectory() {
		/** @var $domains Domain[] */
		$fs      = new Filesystem();
		$domains = $this
			->_getDoctrine()
			->getRepository('JboehmLampcpCoreBundle:Domain')->findAll();

		foreach($domains as $domain) {
			$dir   = $domain->getPath() . '/conf';
			$files = glob($dir . '/authuser_*.passwd');

			foreach($files as $filepath) {
				$idStart = strpos($filepath, 'authuser_') + strlen('authuser_');
				$idEnd   = strpos($filepath, '.passwd');
				$id      = intval(substr($filepath, $idStart, ($idEnd - $idStart)));

				$protection = $this
					->_getDoctrine()
					->getRepository('JboehmLampcpCoreBundle:Protection')
					->findOneBy(array(
									 'id'     => $id,
									 'domain' => $domain->getId(),
								));

				if(!$protection) {
					$this->_getLogger()->info('(ProtectionBuilderService) Deleting obsolete AuthUserFile: ' . $filepath);
					$fs->remove($filepath);
				}
			}
		}
	}
}
