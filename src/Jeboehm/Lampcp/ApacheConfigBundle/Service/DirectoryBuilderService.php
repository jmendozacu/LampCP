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
use Jeboehm\Lampcp\ApacheConfigBundle\IBuilder\BuilderServiceInterface;
use Jeboehm\Lampcp\CoreBundle\Entity\Domain;

/**
 * Class DirectoryBuilderService
 *
 * Creates default directorys in the filesystem.
 *
 * @package Jeboehm\Lampcp\ApacheConfigBundle\Service
 * @author  Jeffrey Böhm <post@jeffrey-boehm.de>
 */
class DirectoryBuilderService extends AbstractBuilderService implements BuilderServiceInterface {
    const _root = 'root';
    const _dirs = 'conf,htdocs,logs,php-fcgi,tmp';

    /**
     * Get default directories.
     *
     * @param string $basepath
     *
     * @return array
     */
    protected function _getDefaultDirs($basepath) {
        $directoryname = explode(',', self::_dirs);
        $fullpath      = array();

        foreach ($directoryname as $dn) {
            $fullpath[] = $basepath . '/' . $dn;
        }

        return $fullpath;
    }

    /**
     * Create default directories.
     *
     * @param Domain $domain
     */
    protected function _createDirectorysForDomain(Domain $domain) {
        $fs = new Filesystem();

        $fs->mkdir(array_merge(array($domain->getPath()), $this->_getDefaultDirs($domain->getPath())), 0750);

        $fs->chmod(array_merge(array($domain->getPath()), $this->_getDefaultDirs($domain->getPath())), 0750);

        $fs->chown($domain->getPath(), self::_root); // Domain Root
        $fs->chgrp($domain->getPath(), $domain
            ->getUser()
            ->getGroupname(), true); // Domain Root + Child

        // Child directorys
        $fs->chown($this->_getDefaultDirs($domain->getPath()), $domain
            ->getUser()
            ->getName(), true);
    }

    /**
     * Create directory structure for all domains.
     */
    public function buildAll() {
        foreach ($this->_getAllDomains() as $domain) {
            $this->_createDirectorysForDomain($domain);
        }
    }
}
