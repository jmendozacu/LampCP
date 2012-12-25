<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jboehm\Lampcp\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Subdomain
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Subdomain {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var Domain
	 * @Assert\NotNull()
	 * @ManyToOne(targetEntity="Domain")
	 */
	private $domain;

	/**
	 * @var string
	 * @Assert\NotBlank()
	 * @ORM\Column(name="subdomain", type="string", length=255)
	 */
	private $subdomain;

	/**
	 * @var string
	 * @Assert\NotBlank()
	 * @ORM\Column(name="path", type="string", length=255)
	 */
	private $path;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Konstruktor
	 *
	 * @param Domain $domain
	 */
	public function __construct(Domain $domain) {
		$this->domain = $domain;
	}

	/**
	 * Get domain
	 *
	 * @return Domain
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * Set subdomain
	 *
	 * @param string $subdomain
	 *
	 * @return Subdomain
	 */
	public function setSubdomain($subdomain) {
		$this->subdomain = $subdomain;

		return $this;
	}

	/**
	 * Get subdomain
	 *
	 * @return string
	 */
	public function getSubdomain() {
		return $this->subdomain;
	}

	/**
	 * Set path
	 *
	 * @param string $path
	 *
	 * @return Subdomain
	 */
	public function setPath($path) {
		$this->path = $path;

		return $this;
	}

	/**
	 * Get path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get full domain (subdomain.domain.tld)
	 *
	 * @return string
	 */
	public function getFullDomain() {
		return $this->getSubdomain() . '.' . $this->getDomain()->getDomain();
	}

	/**
	 * Get full path
	 *
	 * @return string
	 */
	public function getFullPath() {
		return $this->getDomain()->getPath() . '/' . $this->getPath();
	}
}
