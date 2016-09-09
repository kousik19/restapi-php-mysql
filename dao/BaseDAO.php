	<?php
	include_once "loader/ClassLoader.php";
	
	class BaseDAO
	{
			private $columns;					//column in table
			private $properties;				//properties in object
			private $type;						//column variable types
			private $table;						//table name
			private $transform;					//transformed from object attributes to column name
			private $server="localhost";		//more flexibility required
			private $dbname="purple_crm";			//			,,
			private $username="root";			//			,,
			private $password="123456";			//			,,
			private $conn;
			private $load;
			
			public function __construct($table)
			{
				$this->load=new ClassLoader;
				$this->load->loadClass(ucfirst($table));
				//initializing private variables
				
				$this->columns=Array();
				$this->type=Array();
				$this->properties=Array();
				$this->table=lcfirst($table);
				$this->transform=$this->load->loadClass("AttributeTransformation");
				
				//creating connection for getting column names
				
				mysql_connect($this->server,$this->username,$this->password);
				mysql_select_db($this->dbname);
				$sql = "show columns from `".$this->transform->transformClassToDb($table)."`";
				$run = mysql_query($sql);
				
				//getting properties from the object
				
				$cls=ucfirst($table);
				$object=new $cls;
				$rc=new ReflectionClass($object);
				$attr=$rc->getProperties();
				
				
				//assigning columns and properties from table and object
				
				while($row = mysql_fetch_assoc($run)){
					array_push($this->columns,$row["Field"]);
					array_push($this->type,preg_replace("/[^a-zA-Z_\s]+/",'',$row["Type"]));	//getting data type of columns
				}
				
				foreach ($attr as $prop) {
					array_push($this->properties,$prop->getName());
				}
				
				//creating connection object
				
				// Create connection
				$this->conn = new mysqli($this->server, $this->username, $this->password,$this->dbname);

				// Check connection
				if ($this->conn->connect_error) {
					die("Connection failed: " . $conn->connect_error);
				} 
			}
			
			/**------------------------------------------to insert data in into table-----------------------------
			 * dynamically query building for insert
			 * into database table
			 * */
			 
			public function insert($obj)
			{
				//generate sql from object
				
				$sql="insert into ".$this->transform->transformClassToDb($this->table)." (";
				$sql2=" values(";
				$i=0;
				//start of insert query building
				foreach($this->properties as $values)
				{
					if(in_array($this->transform->transformClassToDb($values),$this->columns))
					{
						if($obj->{$values}!==NULL)
						{
							$sql=$sql.($this->transform->transformClassToDb($values)).",";
							
							if($this->type[$i]=="int")
								$sql2=$sql2.($obj->{$values}).",";
							else if($this->type[$i]=="blob")
								$sql2=$sql2."'".($obj->{$values})."',";
							else if($this->type[$i]=="varchar" || $this->type[$i]=="date" || strpos($this->type[$i],"enum")!==false)
								$sql2=$sql2."'".($obj->{$values})."',";
							else
								$sql2=$sql2."'".($obj->{$values})."',";
						}
					}
					else
					{
						echo "<br />wrong mapping on attribute ";						/*!!need to be handeled by exception throwing*/
						echo $this->transform->transformClassToDb($values)."<br />";
						//return;
					}
					$i++;
				}
				
				
				
				$sql2=rtrim($sql2, ",").")";
				$sql=rtrim($sql, ",").")".$sql2;
				//query building end
				//echo $sql; 
				//running query
				if($this->conn->query($sql))										//!!need to be handeled by exception throwing 
				{
					return $this->conn->insert_id;										//!!need to generate
				}
				else
				{
					return "error_code";										//!!need to generate
				}
				
			}
			
			/** end of insert function   **/
			
			/**------------------------------------to retreve data from candidate table--------------------------------
			 * dynamically genarated function 
			 * for retriving data from database table
			 * 
			 * */
			 
			public function retrieve($params)
			{
				$ret=Array();
				//creating a new object
				$cls=ucfirst($this->table);
				$obj=new $cls;
				
				//sql statement to execute
				$sql="select * from ".strtolower($this->transform->transformClassToDb($this->table));
				$sql1="";
				
				//if(json_decode(json_encode($params))==null)
						//exit("Wrong json data");
				
				//condition wise filtering
				if(count($params)!=0)
				{
					$filter="";
					$i=0;
					$limit="";
					foreach($params as $key => $val)
					{
						
						if($key==="row-limit")
						{
							$limit=" LIMIT ".$val;
							continue;
						}
						
						if($key==="return-attr")
						{
							if(!is_array($val))
							{
								exit("{\"status\":\"return-attr value must be an array\"}");
							}
							$sql1="select ";
							for($p=0;$p<count($val);$p++)
							{
								if($p==0)
									$sql1=$sql1.strtolower($this->transform->transformClassToDb($val[$p]));
								else
									$sql1=$sql1.",".strtolower($this->transform->transformClassToDb($val[$p]));
							}
							$sql1=$sql1." from ".strtolower($this->transform->transformClassToDb($this->table));
							continue;
						}
						
						if($i==0)
						{
							if(is_array($val))
							{
								$filter=" where ".$this->transform->transformClassToDb($key)."".$val[0]."'".$val[1]."'";
								$i=1;
							}
							else
							{
								$filter=" where ".$this->transform->transformClassToDb($key)."='".$val."'";
								$i=1;
							}
						}
						else
						{
							if(is_array($val))
							{
								$filter=" where ".$this->transform->transformClassToDb($key)."".$val[0]."'".$val[1]."'";
								$i=1;
							}
							else
								$filter=$filter." AND ".$this->transform->transformClassToDb($key)."='".$val."'";
						}
					}
					if($sql1==="")
						$sql=$sql.$filter.$limit;
					else
						$sql=$sql1.$filter.$limit;
				}
				//echo $sql;
				//end of filtering
				
				//running query
				$result=$this->conn->query($sql);							//!!! need to keep it in try catch
				
				//assigning values with candidate object
				if($result!=null)
				{
					if($result->num_rows>0)
					{
						while($row=$result->fetch_assoc())
						{
							$i=0;
							$obj=new $cls;
							foreach($this->properties as $val)
							{
								if(isset($row[$this->columns[$i]]))
									$obj->{$val}=$row[$this->columns[$i]];
								$i++;
							}
							array_push($ret,$obj);
						}
					}
					else
					{
						$arr=array("information"=>"no data found");
						echo json_encode($arr);										//!!!throw exception here
					}
				}
				else
				{
					$arr=array("information"=>"no data found");
					echo json_encode($arr);											//!!!throw exception here
				}
				return $ret;
			}
			
			/**---------end of retrieve function--------------------------**/
			
			
			/**--to remove data from candidate table
			 * remove function for query building
			 * to remove a row from a table
			 * 
			 * */
			public function remove($id)
			{	
				//sql statement to execute to get the data
				$sql="select * from ".$this->transform->transformClassToDb($this->table)." where ".$this->transform->transformClassToDb($this->table)."_id=".$id;
				//echo $sql;
				$result=$this->conn->query($sql);
				
				
				//assigning values with candidate object
				if($result->num_rows>0)
				{
					//sql statement to execute to delete
					$sql="delete from ".$this->transform->transformClassToDb($this->table)." where ".$this->transform->transformClassToDb($this->table)."_id=".$id;
					
					//echo $sql;
					//deleting  using the query
					$this->conn->query($sql);								//need to keep in try catch
					return $id;
				}
				else
				{
					//echo $sql;
					echo "Wrong Id";										//need to be handled by throwing exception
				}
				
			}
			
			/**
			 * When Update method 'll be called 
			 * then the following method 'll be called
			 *  */
			 
			 public function update($obj)
			 {
				 $idattr=$this->table."Id";
				 $id=$obj->{$idattr};
				 if(isset($id))
				 {
					 $this->remove($id);
					 $this->insert($obj);
					 echo "{\"Status\":\"Updated\"}";
				 }
				 else
				 {
					 exit("wrong object to update");
				 }
			 }
			
			
			/**
			 * When Option method 'll be called
			 * then the following function 'll
			 * be called
			 * */
			 
			 public function getMetadata()
			 {
				 $arr=Array();
				 for($i=0;$i<count($this->columns);$i++)
				 {
					 $obj="{".$this->columns[$i].",".$this->type[$i]."}";
					 array_push($arr,$obj);
				 }
				 return $arr;
			 }
	}
	
	?>
	
