<?php namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Field;

/**
 * TODO: Utilize Static Factory.
 * Class FieldManager
 * @method static FieldManager all() FieldManager
 * @package Clake\UserExtended\Classes
 */
class FieldManager extends StaticFactory
{

    private $fields;

    /**
     * @param $name
     * @param $code
     * @param $description
     * @param string $validation
     * @param string $type
     * @param array $flags
     * @param array $data
     * @return Field
     */
	public static function createField($name, $code, $description, $validation = "", $type = UserSettingsManager::UE_FORM_TEXT, $flags = [], $data = [])
	{
		//Ensure that we are saving a unique field.

        // Just return on name not unique, OR, by using the Validator class to specify that it should be unique.
        // #DontReinventTheWheel
		/*$desiredName = $name;
		$instance = 0;
		while (findField($name) != null){
			$instance++;
			$name = $desiredName . $instance;
		}*/
	
	    // This should be done for auto completing the form. We don't want to simply override the code the user wants to use.
		//$code = str_slug($name, "-");

        // Use the Validator class for this as its easier and more effective/verbose
		//if (!isset($name) && !isset($type)){
		//	return false;
		//}
		//TODO check $validation

		$field = new Field();
		$field->name = $name;
		$field->code = $code;
		$field->description = $description;
		$field->type = $type;
		$field->validation = $validation;
		if(!empty($flags)) $field->flags = $flags;
        if(!empty($data)) $field->data = $data;

		$field->save();
		return $field;	
	}

	public static function makeFlags($enabled = false, $registerable = true, $editable = true, $encryptable = false)
    {
        return [
            'enabled' => $enabled,
            'registerable' => $registerable,
            'editable' => $editable,
            'encrypt' => $encryptable,
        ];
    }
	
	public static function updateField($name, $code, $description, $type = UserSettingsManager::UE_FORM_TEXT, $validation = "", $flags = [], $data = []){
		$field = findField($code);
		$field->name = $name;
		$field->code = $code;
		$field->description = $description;
		$field->type = $type;
		$field->validation = $validation;
		if(!empty($flags)) $field->flags = $flags;
        if(!empty($data)) $field->data = $data;

		$field->save();
		return $field;	
	}
	
	/**
	 * Deletes a field
	 * @param $name
	 */
	public static function deleteField($code)
	{
		$field = Field::where('code', $code)->first();
		$field->delete();
	}

	public function allFactory()
    {
        $this->fields = Field::all();
        return $this;
    }
	
	/**
	 * Finds and returns a Field and its properties
	 * @param $code
	 * @return array
	 */
	public static function findField($code)
	{
		return Field::where('code', $code)->first();
	}
	
	/**
     * TODO: I'm not sure this function is needed.
     *
	 * Determines if the given field is requried to register
	 * @param $code
	 * @return boolean
	 */
    public static function isRequried($code)
    {
        $field = Field::where('code', $code)->first();
        return $field->flags['registerable'];
    }
	/*public static function isRequried($name)
	{
		$selection = Field::where('name', $name)->first();
		return ($selection->data['required'])? true : false;
	}*/

    public function getSortedFields()
    {
        $fields = [];

        if(empty($this->fields))
            return [];

        foreach($this->fields as $field)
        {
            $fields[$field["sort_order"]] = $field;
        }

        ksort($fields);

        return $fields;
    }
}