function ajaxCall(url,method,data,callbk)
{
	$.ajax({
		
			url:url,
			
			method:method,
			
			data:data,
			
			dataType:"json",
			
			error:function(){alert("Something went wrong. :(");},
		
			success:function(data)
			{
				callbk(data);
			}
		
		
		})
}
