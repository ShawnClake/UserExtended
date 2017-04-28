# Routes

### Overview
Routes are a method of creating access restrictions on URL's such that you can restrict a page by:
* Users
* Roles
* Groups
* IP's

The best part about UE Routes is they don't add any code bloat to your pages. The restrictions are entirely setup in the backend, and then simply adding the Route component to your layouts will make the restrictions take effect.

### Features
* Whitelist or Blacklist system
* Toggleable cascading parent and child relationship. This allows you to set a restriction on a parent route which cascades down through your child routes
* Utilizes your existing roles, groups, and users for simplistic restriction creation
* Enable/Disable
* The same restriction rule can be used across many different routes
* A whitelist takes priority system. This means you can blacklist an entire group, and then whitelist one user inside of that group. The net result would be no one from that group can access the page except for the whitelsited user.

### Use Cases
* Developer only area
* Paying members only area
* Restricting access to pages that are no longer needed or are undergoing maintenance.

### How to
Simply load up the backend manager and create some routes and then add restrictions to it.

***
