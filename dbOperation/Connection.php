<?php
class Connection
{
	private $server="localhost";		//more flexibility required
	private $dbname="purple_crm";			//			,,
	private $username="root";			//			,,
	private $password="123456";			//			,,
	private $conn;
	
	public function getConncetion()
	{
		$conn = new mysqli($this->server, $this->username, $this->password,$this->dbname);
		
		if ($conn->connect_error) {										//checking connection error
			die("Connection failed: " . $conn->connect_error);
		}
		
		return $conn;
	}
	
	//getter setter methods for the properties
	
	public function __set($property, $value)
	{
		$this->$property=$value;
	}
	public function __get($property)
	{
		return $this->$property;
	}
}
