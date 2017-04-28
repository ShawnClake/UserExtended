<?php namespace Clake\UserExtended\Classes\Feedback;

use Clake\UserExtended\Classes\FeedbackBase;
use Clake\UserExtended\Traits\StaticFactoryTrait;

/**
 * User Extended by Shawn Clake
 * Class FieldFeedback
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 *
 * Field Feedback provides field feedback messages, it supports
 * Flash and div based feedback. You can use both or either or.
 * Example calls look like this:
 *
 * Feedback::with($myValidator)->generate()->flash()->display('#myDiv');
 * Feedback::with($myValidator)->generate()->display('#myDiv');
 * Feedback::with($myValidator)->generate()->flash();
 *
 * @package Clake\Userextended\Controllers
 */
class FieldFeedback extends FeedbackBase
{
    use StaticFactoryTrait;

    /**
     * Generic Flash Messages
     * @return array
     */
    public function customFlashMessages()
    {
        return [
            'success' => 'Field saved successfully!',
            'error'   => 'Field was not saved!',
            'false'   => 'Field was not saved!'
        ];
    }

    /**
     * Generic Div Messages
     * @return array
     */
    public function customDivMessages()
    {
        return [
            'success' => 'Field has been saved.',
            'error'   => '',
            'false'   => 'That code already exists.',
        ];
    }
}