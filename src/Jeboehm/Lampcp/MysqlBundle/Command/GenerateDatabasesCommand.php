<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jeboehm\Lampcp\MysqlBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Jeboehm\Lampcp\CoreBundle\Command\AbstractCommand;
use Jeboehm\Lampcp\CoreBundle\Service\CronService;
use Jeboehm\Lampcp\CoreBundle\Service\ChangeTrackingService;
use Jeboehm\Lampcp\MysqlBundle\Service\MysqlAdminService;
use Jeboehm\Lampcp\MysqlBundle\Service\MysqlSynchronizerService;

/**
 * Class GenerateDatabasesCommand
 *
 * Create, delete and update MySQL databases
 *
 * @package Jeboehm\Lampcp\MysqlBundle\Command
 * @author  Jeffrey Böhm <post@jeffrey-boehm.de>
 */
class GenerateDatabasesCommand extends AbstractCommand {
    /** @var MysqlAdminService */
    protected $_mysqladminservice;

    /** @var MysqlSynchronizerService */
    protected $_mysqlsyncservice;

    /**
     * Get watched entitys
     *
     * @return array
     */
    protected function _getEntitys() {
        $entitys = array(
            'JeboehmLampcpCoreBundle:MysqlDatabase',
        );

        return $entitys;
    }

    /**
     * @return \Jeboehm\Lampcp\MysqlBundle\Service\MysqlAdminService
     */
    protected function _getMysqlAdminService() {
        if (!$this->_mysqladminservice) {
            $this->_mysqladminservice = $this
                ->getContainer()
                ->get('jeboehm_lampcp_mysql.mysqladminservice');
            $this->_mysqlAdminServiceConnect();
        }

        return $this->_mysqladminservice;
    }

    /**
     * Initialize MySQLAdminService
     */
    protected function _mysqlAdminServiceConnect() {
        $this
            ->_getMysqlAdminService()
            ->connect($this
                ->getContainer()
                ->getParameter('database_host'), $this
                ->_getConfigService()
                ->getParameter('mysql.rootuser'), $this
                ->_getConfigService()
                ->getParameter('mysql.rootpassword'), $this
                ->getContainer()
                ->getParameter('database_port'));
    }

    /**
     * Get MysqlSynchronizerService
     *
     * @return \Jeboehm\Lampcp\MysqlBundle\Service\MysqlSynchronizerService
     */
    protected function _getMysqlSynchronizerService() {
        if (!$this->_mysqlsyncservice) {
            $this->_getMysqlAdminService();
            $this->_mysqlsyncservice = $this
                ->getContainer()
                ->get('jeboehm_lampcp_mysql.mysqlsynchronizerservice');
        }

        return $this->_mysqlsyncservice;
    }

    /**
     * Configure command
     */
    protected function configure() {
        $this->setName('lampcp:mysql:generatedatabases');
        $this->setDescription('Generates (and deletes) MySQL Databases');
        $this->addOption('force', 'f', InputOption::VALUE_NONE);
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
        if (!$this->_isEnabled()) {
            $this
                ->_getLogger()
                ->err('(MysqlBundle) Command not enabled!');

            return;
        }

        $run = false;

        if ($input->getOption('force') || $this->_isChanged()) {
            $run = true;
        }

        if ($run) {
            $this
                ->_getLogger()
                ->info('(MysqlBundle) Executing...');

            if ($input->getOption('verbose')) {
                $output->writeln('(MysqlBundle) Executing...');
            }

            $this
                ->_getMysqlSynchronizerService()
                ->createDatabases();
            $this
                ->_getMysqlSynchronizerService()
                ->deleteObsoleteDatabases();
            $this
                ->_getMysqlSynchronizerService()
                ->deleteObsoleteUsers();

            $this
                ->_getCronService()
                ->updateLastRun($this->getName());
        } else {
            if ($input->getOption('verbose')) {
                $output->writeln('(MysqlBundle) No changes detected.');
            }
        }
    }

    /**
     * Checks for changed entitys that are relevant for this task
     *
     * @return bool
     */
    protected function _isChanged() {
        $last = $this
            ->_getCronService()
            ->getLastRun($this->getName());

        /**
         * First run
         */
        if (!$last) {
            return true;
        } else {
            /**
             * Find entities newer than $last
             */
            foreach ($this->_getEntitys() as $entity) {
                $result = $this
                    ->_getChangeTrackingService()
                    ->findNewer($entity, $last);

                if (count($result) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get cron service
     *
     * @return CronService
     */
    protected function _getCronService() {
        return $this
            ->getContainer()
            ->get('jeboehm_lampcp_core.cronservice');
    }

    /**
     * Get change tracking service
     *
     * @return ChangeTrackingService
     */
    protected function _getChangeTrackingService() {
        return $this
            ->getContainer()
            ->get('jeboehm_lampcp_core.changetrackingservice');
    }

    /**
     * Get "enabled" from config service
     *
     * @return string
     */
    protected function _isEnabled() {
        return $this
            ->_getConfigService()
            ->getParameter('mysql.enabled');
    }
}
