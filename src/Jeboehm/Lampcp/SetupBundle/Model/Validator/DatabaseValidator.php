<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jeboehm\Lampcp\SetupBundle\Model\Validator;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Jeboehm\Lampcp\SetupBundle\Model\Form\Database;

/**
 * Class DatabaseValidator
 *
 * @package Jeboehm\Lampcp\SetupBundle\Model\Validator
 * @author  Jeffrey Böhm <post@jeffrey-boehm.de>
 */
class DatabaseValidator extends AbstractValidator
{
    /**
     * Validate database.
     *
     * @param Database $config
     *
     * @return ValidationResult
     */
    public function validate(Database $config)
    {
        $result        = new ValidationResult();
        $configuration = new Configuration();
        $params        = array(
            'dbname'   => $config->database_name,
            'user'     => $config->database_user,
            'password' => $config->database_password,
            'host'     => $config->database_host,
            'driver'   => 'pdo_mysql',
        );

        try {
            if (empty($config->database_name)) {
                throw new \Exception('Database must not be empty!');
            }

            if (empty($config->database_host)) {
                throw new \Exception('Hostname must not be empty!');
            }

            $connection = DriverManager::getConnection($params, $configuration);
            $connection->connect();

            $result->setSuccessful(true);
        } catch (\Exception $e) {
            $result
                ->setSuccessful(false)
                ->setMessage($e->getMessage());
        }

        return $result;
    }
}
