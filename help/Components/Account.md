# Account

### Use cases
* User registration
* Logging in
* Logging out
* Changing user settings

### Component Properties
Activation code param is only optionally needed on registration

### Template Data Getters
* createSettings()  Returns a list of dynamic settings which are makred as registerable
* updateSettings()  Returns a list of dynamic settings which are marked as editable
* user()  Returns the user object for the logged in user
* signUp()  Returns whether or not we are using email or username to register

### Handlers
* onUpdate()  Called to update user settings and details
* onRegister()  Called to preform a registration
* onLogin()  Called to authenticate and login a user
* onLogout()  Logs out a user

### Usage Examples
To render any of the account feature you will have to manually specify the partial you wish to use.

In the future, this will be integrated into the component inspector.

{% partial 'account::update' %}  - User Settings

{% partial 'account::signup' %}  - User Registration

{% partial 'account::login' %}   - User Login

{% partial 'account::logout' %}  - User Logout

***
