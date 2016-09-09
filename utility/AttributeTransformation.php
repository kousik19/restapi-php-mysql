<?php

class AttributeTransformation
{
	public function transformClassToDb($str)
	{
		$i=1;
		$ch="";
		if(preg_match_all('/[A-Z]/', $str,$matches, PREG_OFFSET_CAPTURE))	/*getting if there is any capital letter */
		{
			//$len=strlen($str);
			
			for($i=0;$i<strlen($str);$i++)
			{
				if($str{$i}===strtoupper($str{$i}) && $i!=0)
				{
					$str = substr_replace($str,"_".strtolower($str{$i}), $i, 1);
				}
			}
			return strtolower($str);
			
		}
		else
			return $str;													/*if no capital letter return the original string*/
	}
	
	public function transformDbToClass($str)
	{
		$parts=explode("_",$str);
		for($i=0;$i<count($parts);$i++)
			$parts[$i]=ucfirst($parts[$i]);
		$str=implode("",$parts);
		return lcfirst($str);
	}
}


?>
