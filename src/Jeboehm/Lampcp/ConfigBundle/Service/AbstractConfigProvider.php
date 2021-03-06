<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jeboehm\Lampcp\ConfigBundle\Service;

use Doctrine\ORM\EntityManager;
use Jeboehm\Lampcp\ConfigBundle\Exception\NoGroupProvidedException;
use Jeboehm\Lampcp\CoreBundle\Entity\ConfigEntity;
use Jeboehm\Lampcp\CoreBundle\Entity\ConfigGroup;

/**
 * Class AbstractConfigProvider
 *
 * Provides useful methods for configuration providers.
 *
 * @package Jeboehm\Lampcp\ConfigBundle\Service
 * @author  Jeffrey Böhm <post@jeffrey-boehm.de>
 */
abstract class AbstractConfigProvider {
    /** @var EntityManager */
    private $_em;

    /** @var ConfigService */
    private $_config;

    /** @var ConfigGroup */
    private $_lastGroup;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param ConfigService $config
     */
    public function __construct(EntityManager $em, ConfigService $config) {
        $this->_em     = $em;
        $this->_config = $config;
    }

    /**
     * Get config service.
     *
     * @return ConfigService
     */
    protected function _getConfig() {
        return $this->_config;
    }

    /**
     * Get doctrine.
     *
     * @return EntityManager
     */
    protected function _getDoctrine() {
        return $this->_em;
    }

    /**
     * Initialize config entities.
     *
     * @return void
     */
    abstract public function init();

    /**
     * Create group.
     *
     * @param string $name
     *
     * @return ConfigGroup
     */
    protected function _createGroup($name) {
        /** @var $group ConfigGroup */
        $name  = strtolower($name);
        $group = $this
            ->_getDoctrine()
            ->getRepository('JeboehmLampcpCoreBundle:ConfigGroup')
            ->findOneBy(array('name' => $name));

        if (!$group) {
            $group = new ConfigGroup();
            $group->setName($name);

            $this
                ->_getDoctrine()
                ->persist($group);
            $this
                ->_getDoctrine()
                ->flush();
        }

        $this->_lastGroup = $group;

        return $group;
    }

    /**
     * Create entity.
     *
     * @param string      $name
     * @param integer     $type
     * @param ConfigGroup $group
     * @param string      $default
     *
     * @throws NoGroupProvidedException
     * @return AbstractConfigProvider
     */
    protected function _createEntity($name, $type, ConfigGroup $group = null, $default = '') {
        if (!$group && !$this->_lastGroup) {
            throw new NoGroupProvidedException();
        }

        $name   = strtolower($name);
        $entity = $this
            ->_getDoctrine()
            ->getRepository('JeboehmLampcpCoreBundle:ConfigEntity')
            ->findOneBy(array(
                             'name'        => $name,
                             'configgroup' => $group,
                        ));

        if (!$entity) {
            $entity = new ConfigEntity();
            $entity
                ->setName($name)
                ->setConfiggroup($group)
                ->setType($type)
                ->setValue($default);

            $this
                ->_getDoctrine()
                ->persist($entity);
            $this
                ->_getDoctrine()
                ->flush();
        }

        return $this;
    }
}
