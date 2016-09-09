<?php
include_once "loader/ClassLoader.php";

class Controller
{
	private $load;
	private $view;
	
	public function setUp($entity,$params,$method)
	{
		$load=new ClassLoader;
		
		$cls=$load->loadClass(ucfirst($entity)."Operation");
		
		if($method=="GET")
			$this->view=$cls->getData($params);
		else if($method=="POST")
			$this->view=$cls->insertData($params);
		else if($method=="PUT")
			$this->view=$cls->updateData($params);
		else if($method=="DELETE")
			$this->view=$cls->deleteData($params);
		else if($method=="OPTIONS")
			$this->view=$cls->metaData($params);
	}
	
	//getter setter methods for the properties

	
		public function __set($property, $value){
			$this->$property=$value;
		}
		public function __get($property){
			return $this->$property;
		}
}
