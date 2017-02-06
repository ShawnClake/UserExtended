#User Extended

## Overview
User Extended provides simple components and User Utility functions for complex interactions with users.

User Extended currently offers friends lists, role management, and User Utilities.

User Extended is typically a dependency to my other plugins.

## Installation
1. Ensure you install the RainLab.User plugin for OctoberCMS first
2. Install this plugin and run
        php artisan october:up
3. You're done :)

## Usage
* Just add the components you require to a page and everything should work out of the box
* Feel free to add your own classes which extend mine

[Check out the bug tracker and feature planner](https://docs.google.com/spreadsheets/d/1_-f5fTYbRb5FWZ0BI2wF9xhEJMZtzMtFxh-4T_GWdXA/edit?usp=sharing)

## Feature List
#### As of 1.0.3
* Frontend User role management in the form of Groups.
* Restrict access to pages or parts of a page using the UserGroups component
* List, send, and accept friend requests using the ListFriendRequests component and the UserList component
* List your friends using the ListFriends component
* Utility user functions which can be used across other plugins and code

#### As of 1.0.8
* Adding a public profile comment system
* Searching for users via name, email, or username
* Deleting friends

#### As of 1.0.22
* Added Timezones and a Twig filter 'timezonify' to adjust Timestamps to a users timezone.
* Added the Timezonable trait which when added to a model will automatically convert model fields to the logged in users timezone.
* Added the concept of Roles. A user can be a part of many groups, but only one role within that group.
  * Use case 1: A blogging website has a group called 'writers'. Within that group their are the roles 'Senior Writer', 'Junior Writer', 'Editor'
* Initial work on a backend UI. Currently supports the managing of Groups and Roles.
* Initial work on group hierarchy, and promotion and demotion system.
* Bug Fixes

#### As of 1.1.00
* Added User Extended modules
* Improved role manager significantly
* Consolidated components into: Friends, User, and Account
* Rewrote much of the code

## Planned Features
* Blocking friends
    * Unblock as well
* Adding a service provider
* Adding an easy way to pragmatically change a users group
    * User utility functions
* Adding a better User settings page
    * Actually functions properly
* Adding a rating system for profiles
    * Thumbs ups ?
    * Likes ?
    * Stars ?
    * Customizable
* Adding a private messaging system
    * Threaded view
    * Inbox view
    * Instant message view
* Adding better email support for user functions: friend requests, accept requests, group changes, messages, comments
    * Email templates for each
    * Ability to change the email template name
* Fleshing our the backend UI
    * Individual Role Management
    * Adding permissions to use the Role manager
    * Removing the Group Manager and integrating it into the Role Manager
* Fleshing out the group Hierarchy w/ promotion/demotion system
* Component to require a role and/or group required to access a page
    * Redirect otherwise
* Users can have a 'library' of images uploaded
    * Permissions to view
    * Name, description
    * Can be sorted into 'categories' or 'albums'
* Support for adding a currency which users can posses. 
    * Customizable in images, name, descriptions, amounts (More than one type of currency)
    * Useful for forums, message boards, game websites, etc.
* Better email confirmation support
* Better support for using usernames to register and login out of the box

## Roadmap
### Version 1.1.00 - UserExtended Beta Release
* Friends
* Roles
* Timezones
* Settings
* Profiles
* Backend UI

### Version 1.2.00 - UserExtended Pre-Release
* Improved stability
* Improved Documentation
* Cleanup
* Bugfixes
* Focus on stability
* Email templates and sending
* Event hooks

### Version 2.0.00 - UserExtended Core Stable Release
* Stability
* Add lang support
* Completed UserExtended Core.

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

