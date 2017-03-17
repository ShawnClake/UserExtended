<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Field;

class FieldManager extends StaticFactory
{

	/**
	* Creates a new field
	* @param $name
	* @param $description
	* @param $type
	* @param $validation
	* @param $flags
	* 
	* $flags is an array indicating which inputs are true.
	*/
	public static createField($name, $description, $type, $validation, $flags)
	{
		//Ensure that we are saving a unique field.
		$desiredName = $name;
		$instance = 0;
		while (findField($name) != null){
			$instance++;
			$name = $desiredName . $instance;
		}
	
	
		$code = str_slug($name, "-");
		if (!isset($name) && !isset($type)){
			return false;
		}
		//TODO check $validation

		$field = new Field();
		$field->name = $name;
		$field->description = $description;
		$field->type = $type;
		$field->validation = $validation;
		$field->flags = $flast;
		$field->save();
		return $field;	
	}
	
	/**
	* Deletes a field
	* @param $name
	*/
	public static deleteField($name)
	{
		$field = Field::find($name);
		$field->delete();
	}
	
	/**
	* Finds and returns a Field and its properties
	* @param $name
	* @return array
	*/
	private findField($name)
	{
		return Field::where('name', $name)->first();
	}
	
	/**
	* Determines if the given field is requried to register
	* @param $name
	* @return boolean
	*/
	public static isRequried($name)
	{
		$selection = Field::where('name', $name)->first();
		return ($selection->data['required'])? true : false;
	}
}