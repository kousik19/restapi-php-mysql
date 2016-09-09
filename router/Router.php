<?php
include_once "RouterInterface.php";
include_once "loader/ClassLoader.php";

class Router
{
	private static $router;
	private static $view;
	private static $entity;
	private static $method;
	private static $params;
	
	private $load;
	
	private function __construct($entity,$method,$params)			//private constructor
	{
		$this->load=new ClassLoader;
		
		$this->setentity($entity);
		$this->setMethod($method);
		$this->setParams($params);
		
		$this->run();
	}
	
	public static function getRouter($entity,$method,$params)		//static function to get router instance
	{
		/*if(isSet(self::$router))
		{
			$router=new Router($entity,$method,$params);
			$obj=new AuthenticationOperation();							//only during first time entry authentication token 'll be generated
			self::$router=$router;
			return $router;
		}*/
		$router=new Router($entity,$method,$params);
		return $router;
		
	}
	
	public function setEntity($entity)
	{
		$this->entity=$entity;
	}
    public function setMethod($method)
    {
		$this->method=$method;
	}
    public function setParams($params)
    {
		$this->params=$params;
	}
	
    public function run()												//actual routing is done here and called from private constructor
    {
		$clsName=strtolower($this->entity);
		$ctrl=$this->load->loadClass("Controller");
		$ctrl->setUp($this->entity,$this->params,$this->method);
	}
}

