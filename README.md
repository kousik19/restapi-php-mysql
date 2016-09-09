A very priliminary rest api for apache-php-mysql stack.

Step 1:
------

	Change the database credentials and name at dbOperation/Connect.php

Step 2:
------

        Change the database credentials and name as dao/BaseDAO.php

Step 3:
------

	Open in browser hostname/folder_name/restart

	Follow the message and resolve issues if any.

Step 4
------

       Everything is ready now. Access the rest api in following url format.

URL Format:

	http://url/uri(application/entity)/param=json
	
	acceptable json key and value json:
	
		1. Any attribute name with value or value with condition for a particular entity
			
				e.g. param={"userId":"2"} or param={"userId":["<","3"]}
				
		2. "row-limit" as a key to get limited numbers of rows
		
				e.g. param={"row-limit":"3"}
				
		3. "return-attr" as a key takes array as value which contains the 
			name of attributes 'll be return if not given all attributes 'll
			be returned
			
				e.g. param={"return-attr":["name","email"]}
				
		http://localhost/purplecrm/user/param={"role":"agent","row-limit":"3","return-attr":["name","email"]}
		
				-This url 'll return top 3 agent's name and email

Step 5:
------

	A very basic ui also now being generated.

	Access that as follow:
	
		hostname/folder_name/folder_name/table_name for getting a form to input data into table

		hostname/folder_name/folder_name/Showtable_name for displaying data in a table

Constraint:
----------

	1.Primary key in the table has to be tablename_id.
	
	2.Database field name must be like multi_word_column_name.

Thats' it. Thank you.

