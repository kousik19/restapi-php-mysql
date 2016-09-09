<?php

class ClassLoader
{
	private $arr=array();
	
	//load class
	public function loadClass($className)
	{
		$paths=$this->listFiles(".");
		
		foreach($paths as $path)
		{
			$parts=explode("/",$path);
			
			if(in_array($className.".php",$parts))
			{
				include_once $path;
				$obj= new $className;
				return $obj;
			}
		}
	}
	
	//load static class
	public function loadStatic($className)
	{
		$paths=$this->listFiles(".");
		
		foreach($paths as $path)
		{
			$parts=explode("/",$path);
			if(in_array($className.".php",$parts))
			{
				include_once $path;
				return $className;
			}
		}
	}
	
	//getting list of files

	private function listFiles($path)
	{
		foreach (new DirectoryIterator($path) as $file) 
		{
				if($file->isDot() || $file->getFileName()==".." || $file->getFileName()==".git" || $file->getFileName()==".htaccess") 
					continue;
				if(is_dir($file)) $this->listFiles($file);
				else
					array_push($this->arr,$path."/".$file);
		}
		return $this->arr;
	}
}
