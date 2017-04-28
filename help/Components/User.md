# User

### Use cases
* Creating a profile page
* Displaying random users
* Searching users
* Displaying a user
* Sending friend requests
* Handling user profile comments
* Visiting user profile

### Component Properties
| === | Profile | Search | Display a User | Random User List |
|:------------:|-------------|--------|----------------|------------------|
| Max Items | ✔ | ✔ | N/A | ✔ |
| URL Param | ✔ >_ :param |  | N/A |  |
| Profile Page | ✔ | ✔ | N/A | ✔ |

### Template Data Getters
* randomUsers(prop maxItems) : UserCollection  Returns a collection of random users who aren't on our friends/block list
* type() : propType  Returns the type of rendering to do: 'random' users, 'single' user, 'search' users, user 'profile' page
* singleUser(prop paramCode) : UserExtended Returns a user 
* user(prop paramCode) : UserExtended Returns a user object
* locked(prop paramCode) : bool Returns whether we are a friend or own the profile we are looking at
* comments(prop paramCode) : CommentsCollection Returns a collection of comments associated with a user
* roles() : RoleCollection  Returns a collection of roles for the logged in  user
* groups() : GroupsCollection  Returns a collection of groups for the logged in user
* userRoles(prop paramCode) : RoleCollection  Returns a collection of roles for an arbitrary user
* userGroups(prop paramCode) : GroupsCollection  Returns a collection of groups for an arbitrary user

### Handlers
* onRequest()  Sends a friend request
* onSearch(post phrase) : PartialRender  Searches for users based on a phrase and returns a list of results
* onFriendUser(prop paramCode)  Sends a friend request to a user
* onComment(prop paramCode, post comment) : PartialRender  Creates a new comment and then refreshes the comment list
* onDeleteComment(post commentid) : PartialRender  Deletes a comment and then refreshes the list
* onVisitProfile($property = null, post id, prop profilePage) : Redirect,false  Redirects a user to a profile page if valid

### Page Variables
* groups  Collection of a users groups

### Usage Examples
* Add the component to a page and utilize the inspector to choose the output type.
* You can also use the AJAX handlers directly if you wish.

***
