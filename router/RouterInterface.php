<?php

interface RouterInterface
{
	public function setController($controller);
    public function setMethod($action);
    public function setParams($params);
    public function run();
}


?>
