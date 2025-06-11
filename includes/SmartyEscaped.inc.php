<?php

use Smarty\Smarty;
class SmartyEscaped extends Smarty 
{ 
    public function assign($templateVar, $value = null, $toEscape = true, $nocache = false)
	{
		if($toEscape)
		{
			if(!is_array($value))
			{
				$value=htmlentities($value);
			}
			else 
			{
				array_walk_recursive($value, function (&$item) {
                    $item = htmlentities($item);
                }
                );
			}
		}
		parent::assign($templateVar, $value, $nocache);
	}
}
