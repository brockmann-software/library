<?php
abstract class bsproman_Controller_Action extends Zend_Controller_Action
{
    protected $_authUser = null;
		
	protected $_acl = null;
		
    protected $_db = null;

    protected $_view = null;
 
    protected $_template = 'main.php';
   
    protected $_vars = array();
		
	protected $_resourceName = '';
/*   
    public function __construct()
    {
		    parent::__construct(Zend_Registry::get('request'),Zend_Registry::get('response'));
        $this->_view = Zend_Registry::get('view');
    }
*/

    public function init() {
		parent::init();
		$this->_authUser = $_SESSION['authUser'];
		if (Zend_Registry::isRegistered('db'))
			$this->_db = Zend_Registry::get('db');
		else throw new Zend_Exception('Kein Modell registriert');
		if (Zend_Registry::isRegistered('acl'))
			$this->_acl = Zend_Registry::get('acl');
		else throw new Zend_Exception('Keine ACL registriert');
        if (Zend_Registry::isRegistered('view')) 
			$this->_view = Zend_Registry::get('view'); 
		else throw new Zend_Exception('Keine Sicht registriert');
			$this->_view->assign('authUser', $this->_authUser);
//		Zend_Debug::dump($this->_view);
	}
		
    public function checkIdentity() {
		$result = $this->_acl->isAllowed($this->_authUser['Role'], strtolower($this->_getParam('controller')), strtolower($this->_getParam('action')));
		return $result;
	}
		
	public function setTemplate($template = '') {
		if ($template=='') {
			throw new Zend_Exception('A string is expected in setTemplate');
		}
		$this->_template = $template;
	}
/*		
		public function getSuppliers() {
		    $select = $this->_db->select();
				$select->from(array('Supplier'=>'Supplier'),
				              array('countOfSuppliers'=>'COUNT(*)'));
				$stmt = $this->_db->query($select);
				$data = $stmt->fetchAll();
				$this->_view->assign('countOfSuppliers', $data[0]['countOfSuppliers']);
		}
		
		public function getProducts() {
		    $select = $this->_db->select();
				$select->from(array('Product'=>'Product'),
				              array('countOfProducts'=>'COUNT(*)'))
							 ->where('Product.id IN (SELECT SupplierProduct.product_id FROM SupplierProduct)');
				$stmt = $this->_db->query($select);
				$data = $stmt->fetchAll();
				$this->_view->assign('countOfProducts', $data[0]['countOfProducts']);
		}
		
		public function getExpiredCerts() {
		    $select = $this->_db->select();
			  $now = new Zend_Date();
			  $select->from(array('Certificate'=>'Certificate'),
			                array('countOfExpiredCerts'=>'COUNT(*)'))
					  	 ->where('Certificate.validy_date < ?', $now->toString('yyyy-MM-dd'));
			  $stmt = $this->_db->query($select);
			  $data = $stmt->fetchAll();
			  $this->_view->assign('countOfExpiredCerts', $data[0]['countOfExpiredCerts']);
				$now->add(1, Zend_Date::MONTH);
				$select1 = $this->_db->select();
			  $select1->from(array('Certificate'=>'Certificate'),
			                array('countOfExpiringInAMonth'=>'COUNT(*)'))
					  	 ->where('Certificate.validy_date < ?', $now->toString('yyyy-MM-dd'));
			  $stmt = $this->_db->query($select1);
			  $data1 = $stmt->fetchAll();
			  $this->_view->assign('countOfExpiringInAMonth', $data1[0]['countOfExpiringInAMonth'] - $data[0]['countOfExpiredCerts']);
	  }
*/		
  	public function __call($methodName, $args) {
        $url = '/index/noroute/'.$methodName;
        $this->_redirect($url);
    }
/*		
    public function __destruct()
    {
		    $this->getSuppliers();
				$this->getProducts();
				$this->getExpiredCerts();
        echo $this->_view->render($this->_template);    
		} */
}
?>
