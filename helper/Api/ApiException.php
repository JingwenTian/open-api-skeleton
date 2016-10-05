<?php 

namespace helper\Api;

class ApiException extends \Exception
{
	
	public function __construct($message, $code) 
	{
		parent::__construct($message, $code);
	}


} 