<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jboehm\Lampcp\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jboehm\Lampcp\CoreBundle\Entity\MysqlDatabase;
use Jboehm\Lampcp\CoreBundle\Form\MysqlDatabaseType;

/**
 * MysqlDatabase controller.
 *
 * @Route("/config/mysqldatabase")
 */
class MysqlDatabaseController extends BaseController {
	/**
	 * Lists all MysqlDatabase entities.
	 *
	 * @Route("/", name="config_mysqldatabase")
	 * @Template()
	 */
	public function indexAction() {
		$em = $this->getDoctrine()->getManager();

		$entities = $em->getRepository('JboehmLampcpCoreBundle:MysqlDatabase')->findByDomain($this->_getSelectedDomain());

		return $this->_getReturn(array(
									  'entities' => $entities,
								 ));
	}

	/**
	 * Finds and displays a MysqlDatabase entity.
	 *
	 * @Route("/{id}/show", name="config_mysqldatabase_show")
	 * @Template()
	 */
	public function showAction($id) {
		$em = $this->getDoctrine()->getManager();

		/** @var $entity MysqlDatabase */
		$entity = $em->getRepository('JboehmLampcpCoreBundle:MysqlDatabase')->find($id);

		if(!$entity) {
			throw $this->createNotFoundException('Unable to find MysqlDatabase entity.');
		}

		$deleteForm = $this->createDeleteForm($id);

		return $this->_getReturn(array(
									  'entity'      => $entity,
									  'delete_form' => $deleteForm->createView(),
								 ));
	}

	/**
	 * Displays a form to create a new MysqlDatabase entity.
	 *
	 * @Route("/new", name="config_mysqldatabase_new")
	 * @Template()
	 */
	public function newAction() {
		$entity = new MysqlDatabase($this->_getSelectedDomain());
		$form   = $this->createForm(new MysqlDatabaseType(), $entity);

		return $this->_getReturn(array(
									  'entity' => $entity,
									  'form'   => $form->createView(),
								 ));
	}

	/**
	 * Creates a new MysqlDatabase entity.
	 *
	 * @Route("/create", name="config_mysqldatabase_create")
	 * @Method("POST")
	 * @Template("JboehmLampcpCoreBundle:MysqlDatabase:new.html.twig")
	 */
	public function createAction(Request $request) {
		$entity = new MysqlDatabase($this->_getSelectedDomain());
		$form   = $this->createForm(new MysqlDatabaseType(), $entity);
		$form->bind($request);

		if($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($entity);
			$em->flush();

			return $this->redirect($this->generateUrl('config_mysqldatabase_show', array('id' => $entity->getId())));
		}

		return $this->_getReturn(array(
									  'entity' => $entity,
									  'form'   => $form->createView(),
								 ));
	}

	/**
	 * Displays a form to edit an existing MysqlDatabase entity.
	 *
	 * @Route("/{id}/edit", name="config_mysqldatabase_edit")
	 * @Template()
	 */
	public function editAction($id) {
		$em = $this->getDoctrine()->getManager();

		/** @var $entity MysqlDatabase */
		$entity = $em->getRepository('JboehmLampcpCoreBundle:MysqlDatabase')->find($id);

		if(!$entity) {
			throw $this->createNotFoundException('Unable to find MysqlDatabase entity.');
		}

		$editForm   = $this->createForm(new MysqlDatabaseType(true), $entity);
		$deleteForm = $this->createDeleteForm($id);

		return $this->_getReturn(array(
									  'entity'      => $entity,
									  'edit_form'   => $editForm->createView(),
									  'delete_form' => $deleteForm->createView(),
								 ));
	}

	/**
	 * Edits an existing MysqlDatabase entity.
	 *
	 * @Route("/{id}/update", name="config_mysqldatabase_update")
	 * @Method("POST")
	 * @Template("JboehmLampcpCoreBundle:MysqlDatabase:edit.html.twig")
	 */
	public function updateAction(Request $request, $id) {
		$em = $this->getDoctrine()->getManager();

		/** @var $entity MysqlDatabase */
		$entity = $em->getRepository('JboehmLampcpCoreBundle:MysqlDatabase')->find($id);

		if(!$entity) {
			throw $this->createNotFoundException('Unable to find MysqlDatabase entity.');
		}

		$oldPassword = $entity->getPassword();

		$deleteForm = $this->createDeleteForm($id);
		$editForm   = $this->createForm(new MysqlDatabaseType(true), $entity);
		$editForm->bind($request);

		if($editForm->isValid()) {
			if(!$entity->getPassword()) {
				$entity->setPassword($oldPassword);
			} else {
				$entity->setPassword($entity->getPassword());
			}

			$em->persist($entity);
			$em->flush();

			return $this->redirect($this->generateUrl('config_mysqldatabase_edit', array('id' => $id)));
		}

		return $this->_getReturn(array(
									  'entity'      => $entity,
									  'edit_form'   => $editForm->createView(),
									  'delete_form' => $deleteForm->createView(),
								 ));
	}

	/**
	 * Deletes a MysqlDatabase entity.
	 *
	 * @Route("/{id}/delete", name="config_mysqldatabase_delete")
	 * @Method("POST")
	 */
	public function deleteAction(Request $request, $id) {
		$form = $this->createDeleteForm($id);
		$form->bind($request);

		if($form->isValid()) {
			$em = $this->getDoctrine()->getManager();

			/** @var $entity MysqlDatabase */
			$entity = $em->getRepository('JboehmLampcpCoreBundle:MysqlDatabase')->find($id);

			if(!$entity) {
				throw $this->createNotFoundException('Unable to find MysqlDatabase entity.');
			}

			$em->remove($entity);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('config_mysqldatabase'));
	}

	private function createDeleteForm($id) {
		return $this->createFormBuilder(array('id' => $id))
			->add('id', 'hidden')
			->getForm();
	}
}
