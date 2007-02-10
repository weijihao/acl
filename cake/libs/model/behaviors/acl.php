<?php
/* SVN FILE: $Id$ */
/**
 * Tree behavior class.
 *
 * Enables a model object to act as a node-based tree.
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c)	2006, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package			cake
 * @subpackage		cake.cake.libs.model
 * @since			CakePHP v 1.2.0.4487
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
if (!defined('ACL_DATABASE')) {
	define('ACL_DATABASE', 'default');
}
/**
 * Short description for file
 *
 * Long description for file
 *
 * @package		cake
 * @subpackage	cake.cake.libs.model.behaviors
 */
class AclBehavior extends ModelBehavior {

/**
 * Maps ACL type options to ACL models
 *
 * @var array
 * @access protected
 */
	var $__typeMaps = array('requester' => 'Aro', 'controlled' => 'Aco');
/**
 * Sets up the configuation for the model, and loads ACL models if they haven't been already
 *
 * @param mixed $config
 * @return void
 */
	function setup(&$model, $config = array()) {
		if (is_string($config)) {
			$config = array('type' => $config);
		}
		$this->settings[$model->name] = am(array('type' => 'requester'), $config);
		$type = $this->__typeMaps[$this->settings[$model->name]['type']];

		if (!ClassRegistry::isKeySet($type)) {
			uses('controller' . DS . 'components' . DS . 'dbacl' . DS . 'models' . DS . 'aclnode');
			uses('controller' . DS . 'components' . DS . 'dbacl' . DS . 'models' . DS . 'aco');
			uses('controller' . DS . 'components' . DS . 'dbacl' . DS . 'models' . DS . 'aro');
			uses('controller' . DS . 'components' . DS . 'dbacl' . DS . 'models' . DS . 'permission');
			$object =& new $type();
		} else {
			$object =& ClassRegistry::getObject($type);
		}
		$model->{$type} =& $object;
		if (!method_exists($model, 'parentNode')) {
			trigger_error("Callback parentNode() not defined in {$model->name}", E_USER_WARNING);
		}
	}
/**
 * Retrieves the Aro/Aco node for this model
 *
 * @param mixed $ref
 * @return array
 */
	function node(&$model, $ref = null) {
		$type = $this->__typeMaps[low($this->settings[$model->name]['type'])];
		if (empty($ref)) {
			$ref = array('model' => $model->name, 'foreign_key' => $model->id);
		}
		return $model->{$type}->node($ref);
	}
/**
 * Creates a new ARO/ACO node bound to this record
 *
 * @param boolean $created True if this is a new record
 * @return void
 */
	function afterSave(&$model, $created) {
		if ($created) {
			$type = $this->__typeMaps[low($this->settings[$model->name]['type'])];
			$model->{$type}->create();
			$model->{$type}->save(array(
				'parent_id'		=> $model->parentNode(),
				'model'			=> $model->name,
				'foreign_key'	=> $model->id
			));
		}
	}
/**
 * Destroys the ARO/ACO node bound to the deleted record
 *
 * @return void
 */
	function afterDelete(&$model) {
		$node = $this->node($model);
		$type = $this->__typeMaps[low($this->settings[$model->name]['type'])];
		$model->{$type}->delete($node['id']);
	}
}

?>