<?php namespace Clake\UserExtended\Classes;

use October\Rain\Support\Facades\Flash;
use Illuminate\Validation\Validator;

/**
 * User Extended by Shawn Clake
 * Abstract Class FeedbackBase
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 *
 * FeedbackBase is the base class for creating feedback classes from. It supports
 * Flash and div based feedback. You can use both or either or.
 * Example calls look like this:
 *
 * Feedback::with($myValidator)->generate()->flash()->display('#myDiv');
 * Feedback::with($myValidator)->generate()->display('#myDiv');
 * Feedback::with($myValidator)->generate()->flash();
 *
 * @method static FeedbackBase with($validator, $flashErrors = false, $flash = [], $div = []) FeedbackBase
 * @package Clake\Userextended\Controllers
 */
abstract class FeedbackBase
{
    /**
     * FLash messages
     * Includes success, error, and false
     * @var array
     */
    private $flash = [];

    /**
     * Div messages
     * Includes success, error, and false
     * @var array
     */
    private $div = [];

    /**
     * Validator Instance
     * @var Validator
     */
    private $validator;

    /**
     * Feedback status
     * Includes -1 => Failure, 0 => False, 1 => Success
     * @var Integer
     */
    private $status;

    /**
     * String to output in a flash message
     * @var
     */
    private $flashOutput;

    /**
     * String to output in the destination div
     * @var
     */
    private $divOutput;

    /**
     * Must be overridden by child classes in order to have individual feedback
     * Must return an array
     * @return array
     */
    abstract public function customFlashMessages();

    /**
     * Must be overridden by child classes in order to have individual feedback
     * Must return an array
     * @return array
     */
    abstract public function customDivMessages();

    /**
     * Factory method for setting up the custom flash and div messages and adding in the validator
     * @param $validator
     * @param array $flash
     * @param array $div
     * @return $this
     */
    public function withFactory($validator, $flashErrors = false, $flash = [], $div = [])
    {
        if(empty($flash))
            $this->flash = $this->customFlashMessages();
        else
            $this->flash = $flash;

        if(empty($div))
            $this->div = $this->customDivMessages();
        else
            $this->div = $div;

        $this->validator = $validator;

        $this->generate($flashErrors);

        return $this;
    }

    /**
     * Generates the feedback strings for flash and divs
     * Also sets the feedback status
     */
    public function generate($flashErrors)
    {
        if($this->validator === false) {
            $this->status = 0;

            $this->divOutput = '<span class="text-warning">';
            $this->divOutput .= $this->div['false'];
            $this->divOutput .= '</span>';

            $this->flashOutput = $this->flash['false'];
        } else if($this->validator->fails()) {
            $this->status = -1;

            $this->divOutput = '<span class="text-danger">' . $this->div['error'];
            $errors = json_decode($this->validator->messages());

            $errorStr = '';
            foreach($errors as $error)
            {
                $errorStr .= implode(' ', $error) . ' ';
            }
            $this->divOutput .= $errorStr . '</span>';

            $this->flashOutput = $this->flash['error'];

            if($flashErrors)
                $this->flashOutput .= ' ' . $errorStr;
        } else {
            $this->status = 1;

            $this->divOutput = '<span class="text-success">';
            $this->divOutput .= $this->div['success'];
            $this->divOutput .= '</span>';

            $this->flashOutput = $this->flash['success'];
        }
    }

    /**
     * Flashes the feedback to the screen
     */
    public function flash()
    {
        if($this->status === 1)
            Flash::success($this->flashOutput);
        else if($this->status === -1)
            Flash::error($this->flashOutput);
        else
            Flash::warning($this->flashOutput);

        return $this;
    }

    /**
     * Returns an array with the key being a destination div and the value being the HTML to put in it.
     * Works out of the box for backend pages, might not be as simple for front end pages.
     * Returning the contents of this from an AJAX handler should update the applicable divs.
     * @param $destinationDiv
     * @return array
     */
    public function display($destinationDiv)
    {
        return [$destinationDiv => $this->divOutput];
    }

}