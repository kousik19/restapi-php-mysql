<?php
include_once "loader/ClassLoader.php";

@header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, UPDATE, DELETE");

$load=new ClassLoader();//autometic class loader

$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$appServer= $_SERVER['HTTP_HOST'];
$uri=$_SERVER['REQUEST_URI'];

$components=split("/",$uri);
$appName=$components[1];

session_start();

//login temporary solution

if(!isset($_SESSION["uid"]))
{
	if(isset($_SERVER["HTTP_LOGINREQ"]))
	{
		$_SESSION["uid"]=$_SERVER["HTTP_UID"];
		$_SESSION["utype"]=$_SERVER["HTTP_UTYPE"];
	}
	else
	{
		if(count($components)>3)
		{
			if($components[3]!="login" && $components[2]!="userlogin" && strpos($components[3],'=')==false)
			{
				header("location:/purplecrm/userlogin/login");
				exit();
			}
		}
		else
		{
			header("location:/purplecrm/userlogin/login");
			exit();
		}
	}
}

//temporary solution

//logout temporary solution
if(isset($_SERVER["HTTP_LOGOUTREQ"]))
{
	session_destroy();
}
//logout temporary solution

if($components[2]=="")
{
	/*echo "<center>";
	echo "<h1>Welcome To This Framework</h1>";
	echo "<h2>Author:Kousik Mandal</h2>";
	echo "<h3>Version:1</h3>";*/
}
else if(count($components)==3)
{
	$entity=$components[2];
	
	if($entity=="restart")							//if restart needed after database change
	{
		$restart=$load->loadClass("RestartApp");
	}
	else 										  	//normal routing operation
	{
		$attr=apache_request_headers();
		$arr=array();
		$json=file_get_contents('php://input');
		$method=$_SERVER['REQUEST_METHOD'];
		$arr=json_decode($json);
	
		if($method=="POST" || $method=="PUT" || $method=="DELETE")
		{
			$arr=json_decode($json);
			if($arr!=null)
			{
				$rout=$load->loadStatic("Router");
				$router=$rout::getRouter($entity,$method,(array)$arr);
			}
			else
			{
				$json=str_replace('&','","',$json);					//making
				$json='{"'.str_replace('=','":"',$json).'"}';		//a
				$arr=json_decode(urldecode($json));					//json object
				
				$arr=get_object_vars($arr);
				$rout=$load->loadStatic("Router");
				$router=$rout::getRouter($entity,$method,$arr);
			}
		}
		else if($method=="GET" || $method=="OPTIONS")
		{
			$arr=json_decode($json);
			$rout=$load->loadStatic("Router");
			
			$router=$rout::getRouter($entity,$method,(array)$arr);
		}
	}
}
else if(count($components)>=4 && strpos($components[3],'=')!==false)
{
	$entity=$components[2];
	$method=$_SERVER['REQUEST_METHOD'];
	
	$param=rawurldecode($components[3]);
	$filter=explode('=',$param);
	$arr=json_decode($filter[1]);
	$rout=$load->loadStatic("Router");
		
	$router=$rout::getRouter($entity,$method,(array)$arr);
}
else if(count($components)==4)
{
	$view=$components[3];
	if($view!="")
		include("view/".$view.".html");
	else
		include("view/index.html");
	exit();
}
