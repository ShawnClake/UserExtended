<?php namespace Clake\Userextended\Components;


use Auth;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use ApplicationException;

class Notifications extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Notifications Component',
            'description' => 'Display user and site-wide notifications'
        ];
    }

    public function defineProperties()
    {
        return [
            'recordsPerPage' => [
                'title'   => 'Records per page',
                'comment' => 'Number of notifications to display per page',
                'default' => 7
            ],
            'includeAssets' => [
                'title'   => 'Include assets',
                'comment' => 'Inject the JavaScript and Stylesheet used by the default component markup',
                'type'    => 'checkbox',
                'default' => true
            ]
        ];
    }

    public function onRun()
    {
        if (!Auth::getUser()) {
            return;
        }

        if ($this->property('includeAssets')) {
            $this->addCss('assets/css/notifications.css');
            $this->addJs('assets/js/notifications.js');
        }

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['recordsToDisplay'] = $this->getRecordCountToDisplay();
        $this->page['hasNotifications'] = $this->hasNotifications();
    }

    public function hasNotifications()
    {
        return $this->getUnreadQuery()->count() > 0;
    }

    public function unreadNotifications($recordsToDisplay = null)
    {
        if (!$recordsToDisplay) {
            $recordsToDisplay = $this->getRecordCountToDisplay();
        }

        return $this->getUnreadQuery()->paginate($recordsToDisplay);
    }

    //
    // AJAX
    //

    public function onLoadNotifications()
    {
        $this->prepareVars();
        $this->page['notifications'] = $this->unreadNotifications();
    }

    public function onLoadOlderNotifications()
    {
        $recordsToDisplay = $this->getRecordCountToDisplay() + $this->property('recordsPerPage');

        $this->page['recordsToDisplay'] = $recordsToDisplay;
        $this->page['notifications'] = $this->unreadNotifications($recordsToDisplay);
    }

    public function onMarkAllNotificationsAsRead()
    {
        $this->getUnreadQuery()->update(['read_at' => Carbon::now()]);

        $this->prepareVars();
        $this->page['notifications'] = $this->unreadNotifications();
    }

    //
    // Helpers
    //

    protected function getRecordCountToDisplay()
    {
        return ((int) post('records_per_page')) ?: $this->property('recordsPerPage');
    }

    protected function getUnreadQuery()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You must be logged in');
        }

        return $user->notifications()->applyUnread();
    }
}
