# Friends

### Use cases
* Send friend request
* Delete friend
* Block **user** - Might be moved to a different component
* Visit profiles
* Accept friend request
* Decline friend request
* Display friends list
* Display friend requests

### Component Properties
| === | Friend Requests | Friends List |
|:------------:|-----------------|--------------|
| Max Items | ✔ | ✔ |
| URL Param |  |  |
| Profile Page | ✔ | ✔ |

### Template Data Getters
* type() : propType  Returns whether we want to render a friends list or a friend request list
* friendsList() : UserCollection  Returns a list of a users friends
* friendRequests() : UserCollection  Returns a list of friend requests for the logged in user

### Handlers
* onDelete(post userId)  Removes a friend from the logged in users friends list
* onBlock(post userId)  Blocks a user from the logged in users friends list
* onVisitProfile(prop profilePage)  Redirects to a users profile page
* onAccept(post userId)  Accepts a friend request
* onDecline(post userId)  Declines a friend request
* onRequest(post userId)  Sends a friend request

### Usage Examples
* Add the component to a page and use the inspector to choose an output type.
* You can also use the AJAX handlers directly if you would like to utilize them in your own plugin.

Current output types:
* Friends list
* Friend requests

