<?php 
class bsproman_Upload {

		protected $_reset_doubles = false;
		
		protected $_attDir = '';
		
		protected $_files = array();
		
		protected $_debug = false;
		
    	function __construct($files =array(), $reset_doubles = false, $attDir='', $debug=false) {
		    $this->_files = $files;
		    $this->_debug = $debug;
			$this->_reset_doubles = $reset_doubles;
			if ($attDir=='') {
				$config = Zend_Registry::get('config');
				if ($config!==NULL) {
					$this->_attDir = $config->framework->attachment_dir.'/';
				}
			}	
		}
		
		public function moveFile($file = array(), $attDir='', $filename='', $reset_doubles=false, $debug=false) {
		    $this->_debug = $debug;
			if (!is_array($file)) {
				    if ($this->_debug) Zend_debug::dump($file);
					throw new Exeption('The given argument is not an array');
				}
				if ($attDir<>'') {
				    $this->_attDir = $attDir.'/';
				}
				if ($filename=='')
     				$fname = preg_replace("/[^A-Za-z0-9.'_-]/", '_', $file['name']);
				else $fname = $filename;
				if ($reset_doubles==true) $this->reset_doubles = $reset_doubles;
      			$fpath = $this->_attDir.$fname;
				$fsufix = 0;
				while (file_exists($fpath)) {
				    if ($this->reset_doubles) {
						    if (!unlink($fpath)) {
								    throw new Exception('The file could not be removed');
								}
								break;
						}
						$fsufix++;
						$ext = substr($fname, strrpos($fname, '.'));
						$fbase = basename($fname, $ext);
						$fname = $fbase.'_'.$fsufix.$ext;
						$fpath = $this->_attDir.$fname;
				}
				if ($this->_debug) echo "Ergebnispfad: $fpath";
				if (move_uploaded_file($file[tmp_name], $fpath)) {
				    return $fname;
				} else {
				    if ($this->_debug) Zend_Debug::dump($file);
					throw new Exception('The file could not be uploaded '.$file['error']);
				    return false;
				}				
		}
}
?>