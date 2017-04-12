# Field Manager

### Overview
Fields are a method of creating inputs which are tied to each user. In other words, its a simple way to add fields which a user can user when he/she registers or updates their settings.

These could include fields such as phone numbers, addresses, invite codes, pin codes, birthday, links, dates, checkboxes, or a variety of others.

Just load up the FieldManager in the backend and define some fields, and their validation settings.

### Features
* Simple backend based field management
* Built in validation
* Currently 11 types of fields are supported with more to come soon
* Saved as JSON so the users table doesn't become crowded
* Optional (enabled by default) rendering of the fields via templates
* Optional encryption on a per field basis
* Optional class and placeholder overrides
* The ability to add any amount of additional attributes to the field as you need. Data attributes included.

### Use Cases
* Creating a temporary invite code field required for user registration
* Adding fields for users to list links to their social media pages
* Allowing users to pick a set of colors or set a future date and time to receive a reminder
* Adding a checkbox users can check if they accept the terms
* Allowing a user to disable email notifications by turning off a switch

### How to
Simply load up the FieldManager is the backend and start creating custom fields

These fields will automatically display on registration and update pages as you specify. You can also programatically retrieve these values and options, but this is ___not___ officially supported yet.
