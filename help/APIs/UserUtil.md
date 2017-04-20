# UserUtil
All functions in User Util are declared as static and should be used like UserUtil::function();

## Function API
getUsers($value, $property = "name") : UserCollection  Preforms a DB query for users with their $property = $value

getUser($value, $property = "id") : UserExtended  Preforms a DB query for the first user with their $property = $value

getRainlabUser($value, $property = "id") : User  Preforms a DB query for the first user with their $property = $value

getLoggedInUser() : User  Returns the Rainlab User model for the logged in user

getLoggedInUsersTimezone() : string  Returns the timezone code for the logged in user

getUserTimezone($value, $property = "id") : string  Returns the timezone code for a user where their $property = $value

castToRainLabUser(UserExtended $user) : User  Preforms a top level cast by transferring only the attributes of the UserExtended object to a User object (Fast)

castToUserExtendedUser($user) : UserExtended  Preforms a top level cast by transferring only the attributes of the User object to an UserExtended object (Fast)

convertToUserExtendedUser($user) : UserExtended  Preforms a low level conversion by preforming a DB query to populate the UserExtended object (Slow)

searchUsers($phrase) : UserExtendedCollection  Searches for users via $phrase. It searches first name, surname, email, and username

getUsersIdElseLoggedInUsersId($userId = null) : int  Gets the user ID for the logged in user, if no user is logged in, it returns the passed ID as a fallback

getUserForUserId($userId = null) : UserExtended  Returns the UserExtended object for the user ID passed in. If the user ID passed in is null, gets logged in user

idIsLoggedIn($userId) : bool  Returns whether or not a passed in ID is the ID of the logged in user

getLoggedInUserExtendedUser() : UserExtended  Gets the logged in user object and converts it to an UserExtended user object before returning it

* * *
