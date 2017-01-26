# Account

### Use cases
* User registration
* Logging in
* Logging out
* Changing user settings

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