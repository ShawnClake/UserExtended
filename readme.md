# User Extended
*** Currently on version v2.1.00 ***

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
* Advanced page restriction using groups
* Friends lists. You can send friend requests and block/delete/accept friends
* User Utility functions which can be used across your own plugins
* User profiles and a profile comment system
* User search. Search for users by name, email or username
* Timezone support! Use the '|timezonify' twig filter. 
* Use the '|relative' twig filter to get a textual relative time stamp (5 seconds ago, 2 months ago)
* Timezonable trait which can be added to models to automagically convert times
* Extensible modules
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
* clake.ue.preregistration(post &$data) : halted. $data contains registration form data. Returning false will cancel registration.
* clake.ue.postregistration(UserExtended &$user). $user contains the final user object before saving it and logging out the user to finalize registration.
* clake.ue.login(User $user). $user contains the user object after authenticating.
* clake.ue.logout(User $user). $user contains the user object after logging out.
* clake.ue.settings.create(UserSettingsManager &$instance). After the user settings instance object has been created
* clake.ue.settings.update(UserSettingsManager &$instance). After the user settings instance object has been created

