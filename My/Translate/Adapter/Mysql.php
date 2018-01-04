<?php
class My_Translate_Adapter_Mysql extends Zend_Translate_Adapter
{
	private $_data = array();
	private $db = null;

	public function __construct($options = array())
	{
		$this->db = Zend_Registry::get('db');
		parent::__construct($options);
	}
	
	protected function _loadTranslationData($table, $locale, array $options = array())
	{
		$this->_data = array();
		$language = 'language';
		$key = 'key';
		$translation = 'translation';
		if (isset($options['columns'])) {
			if (is_array($options['columns'])) {
				$language = $options['columns'][0];
				$key = $options['columns'][1];
				$translation = $options['columns'][2];
			}
		}
		$select = $this->db->select()->from($table, array($language, $key, $translation));
		if (isset($options['view'])) $select->where('view_name=?', $options['view']);
		$translation_records = $this->db->query($select)->fetchAll();
		foreach ($translation_records as $translation_record) 
			$this->_data[$translation_record[$language]][$translation_record[$key]] = $translation_record[$translation];
		return $this->_data;
	}
	
	public function toString()
	{
		return 'Mysql';
	}
}