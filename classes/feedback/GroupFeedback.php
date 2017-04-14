<?php namespace Clake\UserExtended\Classes\Feedback;

use Clake\UserExtended\Classes\FeedbackBase;
use Clake\UserExtended\Traits\StaticFactoryTrait;

/**
 * User Extended by Shawn Clake
 * Class GroupFeedback
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 *
 * Group Feedback provides group feedback messages, it supports
 * Flash and div based feedback. You can use both or either or.
 * Example calls look like this:
 *
 * Feedback::with($myValidator)->generate()->flash()->display('#myDiv');
 * Feedback::with($myValidator)->generate()->display('#myDiv');
 * Feedback::with($myValidator)->generate()->flash();
 *
 * @package Clake\Userextended\Controllers
 */
class GroupFeedback extends FeedbackBase
{
    use StaticFactoryTrait;

    /**
     * Generic Flash Messages
     * @return array
     */
    public function customFlashMessages()
    {
        return [
            'success' => 'Group saved successfully!',
            'error'   => 'Group was not saved!',
            'false'   => 'Group was not saved!'
        ];
    }

    /**
     * Generic Div Messages
     * @return array
     */
    public function customDivMessages()
    {
        return [
            'success' => 'Group has been saved.',
            'error'   => '',
            'false'   => 'That code already exists.',
        ];
    }
}