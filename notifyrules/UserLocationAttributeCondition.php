<?php namespace Clake\Userextended\NotifyRules;

use RainLab\Notify\Classes\ModelAttributesConditionBase;
use ApplicationException;

class UserLocationAttributeCondition extends ModelAttributesConditionBase
{
    protected $modelClass = \Clake\Userextended\Models\UserExtended::class;

    public function getGroupingTitle()
    {
        return 'User attribute';
    }

    public function getTitle()
    {
        return 'User attribute';
    }

    /**
     * Checks whether the condition is TRUE for specified parameters
     * @param array $params Specifies a list of parameters as an associative array.
     * @return bool
     */
    public function isTrue(&$params)
    {
        $hostObj = $this->host;

        $attribute = $hostObj->subcondition;

        if (!$user = array_get($params, 'user')) {
            throw new ApplicationException('Error evaluating the user attribute condition: the user object is not found in the condition parameters.');
        }

        return parent::evalIsTrue($user);
    }
}
