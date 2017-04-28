# Modules

#### How to extend UserExtended

If you are writing a plugin and you would like to use functions from UserExtended or provide your own functions, then this is the right place to come.

UserExtended provides two methods of extensibility. The old method is called User Utilities and the new method is called Modules.

## Modules
Modules are a form of registration which plugins can use to directly interact with other modules as well as UserExtended.

Modules consist of:
* Meta data: $name, $description, $author, $version
* Overridden functions: injectComponents(), injectNavigation(), injectLang(), initialize()
* Extensible functions. While the functions and meta data above are required, extensible functions are entirely up to the module author and can be non existent.

### What are modules for?
Modules allow plugin authors to form a strict contract for what they would like other plugin authors to be able to manipulate and access with their plugin when it comes to User functions.

As an example, imagine you are writing a forum plugin. It may be useful for you to create a module which can return a users latest activity, a users post history, or user post stats. Rather then having other plugin authors snoop through your code and database tables, you can just create some simple extensible functions in your module and other plugin authors can use those directly.

### Okay, so I made a module class, how do I register it?
To register your module, you will have to go into your plugin's plugin.php file and add the line Module::register() inside of the register function.

### My module is registered, now how do I use them?
To use a module, you simply use the following syntax anywhere in your plugin. UserExtended::moduleName()->extensibleFunction($params);

The moduleName is the $name meta data defined within a module registration file.

### What are module best practices?
The name of a module should be in a format like: authorModuleName as this helps prevent naming conflicts.

### What if I want to check if other modules are loaded before providing features? What is this initialize function for?
The initialize function which you override to create a module, is called on each module after all the modules have been registered.

This allows you as a plugin dev to unlock more functionality if various other modules are loaded. 

### What are the injection functions for?
It allows you to add components, lang, navigation and more in the future to UserExtended!

## User Utilities
UserExtended provides a class called UserUtil which has many common User based functions which you may find useful to use in your own plugins.

The long term goal is to move these functions into the UserExtended core module and deprecate User Utilities.

* * *
