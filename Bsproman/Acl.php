<?php
class Bsproman_Acl extends Zend_Acl {
	public function getRoles() {
		return $this->_getRoleRegistry()->_roles;
	}
	
	public function getResources() {
		return $this->_getRoleRegistry()->_resources;
	}
}
?>