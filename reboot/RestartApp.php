<?php
include_once "loader/ClassLoader.php";

class RestartApp
{
	private $load;
	private $columns;
	private $types;
	
	public function __construct()
	{
		$this->load=new ClassLoader;
		$conncls=$this->load->loadClass("Connection");
		$conn=$conncls->getConncetion();
		
		if($conn!=null)echo "database connection established<br>\n";
		$dbname=$conncls->dbname;
		
		$sql="show tables";
		$result=$conn->query($sql);
		
		while($table = $result->fetch_assoc()) 
		{
			$this->columns=array();
			$this->types=array();
			$sql_col = "show columns from `".strtolower($table["Tables_in_".$dbname])."`";
			$columns=$conn->query($sql_col);
			while($col=$columns->fetch_assoc())
			{
				array_push($this->columns,$col["Field"]);
				array_push($this->types,$col["Type"]);
			}
			$this->writeEntity($table["Tables_in_".$dbname],$this->columns);
			$this->writeBl($table["Tables_in_".$dbname],$this->columns);
			//$this->writeJs($table["Tables_in_".$dbname],$col["Field"],$col["Type"]);
			$this->writeView($table["Tables_in_".$dbname],$col["Field"],$col["Type"]);
			unset($this->columns);
		}
	}
	
	//function to write entity class
	private function writeEntity($tableName,$columns)
	{	
		$parts=explode("_",$tableName);
		for($i=0;$i<count($parts);$i++)
			$parts[$i]=ucfirst($parts[$i]);
		$tableName=implode("",$parts);
		
		$entity = fopen("entity/".ucfirst($tableName).".php", "w");
		
		//start of writing
		echo "Start writing entity ".ucfirst($tableName)."<br>\n";
		echo "Please Wait..<br>\n";
		
		$txt="<?php\n\n";
		$txt=$txt."class ".ucfirst($tableName)."{\n";
		foreach($this->columns as $col)
		{
			$txt=$txt."		"."private $".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col).";\n";
		}
		$txt=$txt."\n\n//getter setter methods for the properties\n
	
		public function __set("."$"."property, "."$"."value){\n		"
			."	$"."this->"."$"."property="."$"."value;
		}
		public function __get("."$"."property){
			return "."$"."this->"."$"."property;
		}\n";
		$txt=$txt."}";
		
		fwrite($entity,$txt);
		fclose($entity);
		echo "Write of entity ".ucfirst($tableName)." end<br>\n";
	}
	
	//function to write business logic class
	public function writeBl($tableName,$columns)
	{
		$parts=explode("_",$tableName);
		for($i=0;$i<count($parts);$i++)
			$parts[$i]=ucfirst($parts[$i]);
		$tableName=implode("",$parts);
		
		$bl = fopen("bl/".ucfirst($tableName)."Operation.php", "w");
		//start of writing
		echo "Start writing business logic ".ucfirst($tableName)."<br>\n";
		echo "Please Wait..<br>\n";
		
		$txt="<?php\n\n";
		$txt=$txt."include_once \"loader/ClassLoader.php\";\n";
		$txt=$txt."include_once \"dao/BaseDAO.php\";\n";
		$txt=$txt."\n\nclass ".ucfirst($tableName)."Operation extends BaseDAO{\n\n	";
		$txt=$txt."private "."$"."loader;\n\n	";
		$txt=$txt."public function __construct(){
			parent::__construct('".$tableName."');
			"."$"."this->loader=new ClassLoader;\n
		}";
		
		echo "start of insert data function for".ucfirst($tableName)."<br>\n";
		//insert data function
		$txt=$txt."\n\n	//function to insert data";
		$txt=$txt."\n\n	public function insertData("."$"."params){\n\n	";
		$txt=$txt."	$"."obj="."$"."this->loader->loadClass(\"ucfirst($tableName)\");\n";
		$txt=$txt."		$"."obj="."new StdClass;\n\n";
		
		$i=0;
		foreach($this->columns as $col)
		{
			if($i==0)
				$txt=$txt."		$"."obj->".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."=0;\n";
			else
				$txt=$txt."		$"."obj->".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."="."$"."params[\"".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."\"];\n";
			$i=1;
		}
		$txt=$txt."\n		    "."$"."ret=$"."this->insert("."$"."obj);";
		$txt=$txt."\n		    echo \"{\\\"id\\\":\\\"\"."."$"."ret.\"\\\"}\";\n	}";
		
		echo "Write of business logic insertData function of ".ucfirst($tableName)." <br>\n";
		
		//start of writing of getData function
		
		echo "Start of writing of getdata function of ".ucfirst($tableName)."<br>\n";
		$txt=$txt."\n\n	//function to retrieve all data";
		$txt=$txt."\n\n	public function getData("."$"."params){
		"."$"."collection="."$"."this->retrieve("."$"."params);
			
		"."$"."arr=Array();
		"."$"."ret=Array();";
		$txt=$txt."\n	//iterating over the collection of rows from database		
		foreach("."$"."collection as "."$"."obj)
		{";
		foreach($this->columns as $col)
		{
			$txt=$txt."\n			if($"."obj->".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."!==null)";
			$txt=$txt."\n				$"."arr[\"".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."\"]="."$"."obj->".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col).";";
		}
		
		$txt=$txt."\n\n			array_push("."$"."ret,"."$"."arr);\n		}";
		$txt=$txt."\n		echo json_encode("."$"."ret);\n	}";
		
		echo "End of retrieve function of ".ucfirst($tableName)."<br>\n";
		echo "Start of update function of ".ucfirst($tableName)."<br>\n";
		
		//start of writing of updateData function
		$txt=$txt."\n\n	//function to update data";
		$txt=$txt."\n\n	public function updateData("."$"."params){";
		$txt=$txt."\n		$"."obj="."$"."this->loader->loadClass(\"".ucfirst($tableName)."\");\n\n";
		
		$i=0;
		foreach($this->columns as $col)
		{
			$txt=$txt."		$"."obj->".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."="."$"."params[\"".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."\"];\n";
			$i=1;
		}
		$txt=$txt."\n		$"."this->update("."$"."obj);\n	}";
		
		echo "end of update function for ".ucfirst($tableName)."<br>\n";
		echo "Start of delete function of ".ucfirst($tableName)."<br>\n";
		//delete function
		$txt=$txt."\n\n	//function to delete a data";
		$txt=$txt."\n\n	public function deleteData("."$"."params){	";
		foreach($this->columns as $col)
		{
			$txt=$txt."$"."ret="."$"."this->remove("."$"."params[\"".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."\"]);";
			break;
		}
		$txt=$txt." echo \"{\\\"deleted\\\":\\\"\"."."$"."ret.\"\\\"}\";}";
		echo "End of delete function of ".ucfirst($tableName)."<br>\n";
		
		//delete function
		$txt=$txt."\n\n	//function to get meta data";
		$txt=$txt."\n\n	public function metaData("."$"."params){	echo json_encode("."$"."this->getMetaData());}\n}";
		
		fwrite($bl,$txt);
		fclose($bl);
	}
	
	public function writeView($table,$columns,$datatype)
	{
		$view=fopen("view/".$table.".html","w");
		echo "start writing of view for post of ".$table."<br>\n";
		$txt="<!DOCTYPE html>\n";
		$txt=$txt."<html>\n";
		$txt=$txt."	<head>\n";
		$txt=$txt."		<title>".ucfirst($table)."</title>\n";
		$txt=$txt."		<link rel=\"stylesheet\" type=\"text/css\" href=\"../view/css/grid.css\">\n";
		$txt=$txt."		<link rel=\"stylesheet\" type=\"text/css\" href=\"../view/css/master.css\">\n";
		$txt=$txt."		<link rel=\"stylesheet\" type=\"text/css\" href=\"../view/css/theme.css\">\n";
		$txt=$txt."</head>\n";
		$txt=$txt."</body>\n";
		$txt=$txt."<div class=\"row header secondary-color secondary-text-color\"></div>\n";
		$txt=$txt."<div class=\"row main primary-color primary-text-color\">\n";
		$txt=$txt."<form>\n<table>\n";
		
		$i=0;
		foreach($this->columns as $col)
		{
			if($i!=0)
			{
				$txt=$txt."<tr>\n";
				$txt=$txt."<td>".ucfirst($this->load->loadClass("AttributeTransformation")->transformDbToClass($col))."</td>\n";
				if(strpos($this->types[$i],"varchar")!==false)
					$txt=$txt."<td>"."<input type=text>"."</td>\n";
				else if(strpos($this->types[$i],"int")!==false)
					$txt=$txt."<td>"."<input type=number>"."</td>\n";
				else if(strpos($this->types[$i],"date")!==false)
					$txt=$txt."<td>"."<input type=date>"."</td>\n";
				else
					$txt=$txt."<td>"."<input type=text>"."</td>\n";
				$txt=$txt."</tr>\n";
			}
			$i++;
		}
		$txt=$txt."</table>\n</form>\n";
		$txt=$txt."<button>Submit</button> <button>Reset</button>\n";
		$txt=$txt."</div>\n";
		$txt=$txt."<div class=\"row footer secondary-color secondary-text-color\"></div>\n";
		$txt=$txt."</body>\n";
		$txt=$txt."<script src=\"../view/js/jquery.js\"></script>\n";
		$txt=$txt."<script src=\"../view/js/master.js\"></script>\n";
		$txt=$txt."<script>\n";
		$txt=$txt."$(\"document\").ready(function(){\n";
		$txt=$txt."		$(\"button:first-of-type\").click(function(){\n";
		$txt=$txt."			var json=\"\";\n";
		$i=0;
		foreach($this->columns as $col)
		{
			if($i!=0)
			{
				$txt=$txt."			json=json+\"\\\"".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."\\\"\"+\":\\\"\"+$(\"table tr:nth-child(".$i.") td:last-of-type input\").val()+\"\\\",\";\n";
				if($i==count($this->columns)-1)
					$txt=$txt."			json=json+\"\\\"".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."\\\"\"+\":\\\"\"+$(\"table tr:nth-child(".$i.") td:last-of-type input\").val()+\"\\\"\";\n";
				
			}
			$i++;
		}
		$txt=$txt."			json=\"{\"+json+\"}\";\n";
		$txt=$txt."			ajaxCall(\"../".$table."\",\"POST\",json,function(data){
									location.reload();
								})\n 		})\n \n})\n";
		$txt=$txt."</script>\n";
		$txt=$txt."</html>";
		
		fwrite($view,$txt);
		fclose($view);
		
		echo "end writing of view for post of ".$table."<br>\n";
		
		$view=fopen("view/Show".$table.".html","w");
		echo "start writing of view for get data of ".$table."<br>\n";
		
		$txt="<!DOCTYPE html>";
		$txt=$txt."<html>\n";
		$txt=$txt."	<head>\n";
		$txt=$txt."		<title>".ucfirst($table)."</title>\n";
		$txt=$txt."		<link rel=\"stylesheet\" type=\"text/css\" href=\"../view/css/grid.css\">\n";
		$txt=$txt."		<link rel=\"stylesheet\" type=\"text/css\" href=\"../view/css/master.css\">\n";
		$txt=$txt."		<link rel=\"stylesheet\" type=\"text/css\" href=\"../view/css/theme.css\">\n";
		$txt=$txt."</head>\n";
		$txt=$txt."<body>\n";
		$txt=$txt."	<div class=\"row header secondary-color secondary-text-color\"></div>\n";
		$txt=$txt."	<div class=\"row main showdata primary-color primary-text-color\">\n";
		$txt=$txt."		<table>\n";
		$txt=$txt."		</table>\n";
		$txt=$txt."	</div>\n";
		$txt=$txt."<div class=\"row footer secondary-color secondary-text-color\"></div>\n";
		$txt=$txt."</body>\n";
		$txt=$txt."<script src=\"../view/js/jquery.js\"></script>\n";
		$txt=$txt."<script src=\"../view/js/master.js\"></script>\n";
		$txt=$txt."<script>\n";
		$txt=$txt."	$(\"document\").ready(function(){\n\n";
		$txt=$txt."		ajaxCall(\"../".$table."\",\"GET\",\"\",function(data){\n";
		$txt=$txt."			var html=\"<tr>\";\n";
		foreach($this->columns as $col)
		{
			$txt=$txt."			html=html+\"<th class=\\\"secondary-color secondary-text-color\\\">".ucfirst($this->load->loadClass("AttributeTransformation")->transformDbToClass($col))."</th>\";\n";
		}
		$txt=$txt."			html=html+\"<th class=\\\"secondary-color secondary-text-color\\\">Settings</th>\";\n";
		$txt=$txt."			html=html+\"</tr>\";\n";
		$txt=$txt."			for(var i=0;i<data.length;i++){\n";
		$txt=$txt."				html=html+\"<tr>\";\n";
		foreach($this->columns as $col)
		{		
			$txt=$txt."				html=html+\"<td>\"+data[i].".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."+\"</td>\";\n";
		}
		foreach($this->columns as $col)
		{	
			$txt=$txt."				html=html+\"<td><button onclick=\\\"rem(\"+data[i].".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."+\")\\\">Remove</button><button>Update</button></td>\";\n";
			break;
		}
		$txt=$txt."				html=html+\"</tr>\";\n";
		$txt=$txt."			}\n";
		$txt=$txt."			$(\"table\").html(html);\n";
		$txt=$txt."		})\n";
		$txt=$txt."	})\n";
		$txt=$txt.		"		function rem(id){\n";
		foreach($this->columns as $col)
		{
			$txt=$txt.		"			var json=\"{\\\"".$this->load->loadClass("AttributeTransformation")->transformDbToClass($col)."\\\":\"+id+\"}\";\n";
			$txt=$txt.		"			ajaxCall(\"../".$table."\",\"DELETE\",json,function(data){\n";
			break;
		}
		$txt=$txt.		"			location.reload();\n";
		$txt=$txt.		"		})\n";
		$txt=$txt.		"	}\n";
		$txt=$txt."</script>\n";
		$txt=$txt."</html>";
		
		fwrite($view,$txt);
		fclose($view);
		
		echo "end writing of view for get data of ".$table."<br>\n";
	}
}
