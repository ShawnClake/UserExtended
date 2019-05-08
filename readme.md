# User Extended

## Project Status
* This project has been taken over and is being maintained by Joshua Webb

***Currently on version v2.2.00***
#### New in v2.2.00
        * Avatar picker on update page. {% partial 'account::update' %}
        * Closing, Reopening, Suspending, Deleting accounts. (Currently not accessible in the frontend by default)
        * Reset Roles and Groups back to default from the Role Manager
        * Usernames are now unique, and nicknames have been added
        * Friend states now use a bin 2^n storage pattern. This may change to use a DB table in the future.
        * Route restrictions and access tracking
        * Added more field types including, but not limited to: number, color, date, email, password, file, url
        * Now possible to add, change, remove timezones from the application
        * You can now override the relation between two users.
        * The addition of the [Beta] Module Manager. Currently changing settings for a module has no effect.
        * Improved error/success feedback system for Backend and Frontend validation
*Please see the Module manager in the backend to view a detailed changelog*

## Overview
User Extended provides simple components and User Utility functions for complex interactions with users.

User Extended currently offers friends lists, role management, and User Utilities.

## Dependencies
* RainLab.User http://octobercms.com/plugin/rainlab-user https://github.com/rainlab/user-plugin

## Installation
Install this plugin and run
      
      php artisan october:up

## Usage
* Just add the components you require to a page and everything should work out of the box
* You can create modules to interact with User Extended and other UE modules
* Use the command: `php artisan create:uemodule author.pluginname` in order to scaffold a module class for your own plugins.

[Check out the bug tracker and feature planner](https://github.com/ShawnClake/UserExtended/issues)

## Contributors
* [Shawn Clake](http://shawnclake.com)
* [Quinn Bast](http://www2.cs.uregina.ca/~bast200q/)

## Feature List
* Backend Role/Group management
* Advanced page restriction using groups, roles, users, and IP addresses.
* Modules can be used to inject user code or develop an user API for your plugin
* Friends lists. You can send friend requests and block/delete/accept friends
* User Utility functions which can be used across your own plugins
* User profiles and a profile comment system
* User search. Search for users by name, email or username
* Timezone support! Use the '|timezonify' twig filter. 
* Use the '|relative' twig filter to get a textual relative time stamp (5 seconds ago, 2 months ago)
* Timezonable trait which can be added to models to automagically convert times
* Fields for users. Now you can easily add fields for phone numbers, addresses, pin codes, invite numbers etc.

### Please see the help directory for more information

## Planned Features
[Check out the feature planner](https://github.com/ShawnClake/UserExtended/issues)

## Roadmap
[View our road map](https://github.com/ShawnClake/UserExtended/projects)

## Details
User Extended is not trying to be a social network plugin. We are providing functionality for more complex user functions which have use cases outside of social networks.

Websites specializing in online games, forums, blogs, news etc. can all benefit from User Extended.

## Event List
* [Disabled] clake.ue.preregistration(post &$data) : halted. $data contains registration form data. Returning false will cancel registration.
* [Disabled] clake.ue.postregistration(UserExtended &$user). $user contains the final user object before saving it and logging out the user to finalize registration.
* clake.ue.login(User $user). $user contains the user object after authenticating.
* clake.ue.logout(User $user). $user contains the user object after logging out.
* clake.ue.settings.create(UserSettingsManager &$instance). After the user settings instance object has been created
* clake.ue.settings.update(UserSettingsManager &$instance). After the user settings instance object has been created

