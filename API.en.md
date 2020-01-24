

## InfoClient

This class is designed to actively receive information from the Telegram server and has the following methods:  
  
`login (AuthKey $ authKey [, Proxy $ proxy [, callable $ cb]])` - authorization of the client on the Telegram server.  
* *$authKey* - authorization key received after registering the phone number on the Telegram server.  
* *$proxy* - an object for using a proxy server to operate the library  
* *$cb* - a callback function that will be executed after authorization.  
  
`isLoggedIn ()` - client authorization check  
  
`pollMessage ()` - a method awaiting receipt of messages from the Telegram server  
  
`getChatMembers (int $ id, callable $ onComplete)` - getting chat participants.  
* *$id* - chat id  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`getChannelMembers (int $ id, int $ accessHash, callable $ onComplete)` - getting channel members  
* *$id* - channel id  
* *$accessHash* - hash coming from the server in the ‘access_hash’ channel information field  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`getFullChannel (int $ id, int $ accessHash, callable $ onComplete)` - ???  
  
`getChatMessages (int $ id, int $ limit, int $ since, int $ lastId, callable $ onComplete)` - receive messages from the chat with the ability to filter by time and quantity.  
* *$id* - chat id  
* *$limit* - how many messages are requested from the server  
* *$since* - timestamp with which messages are requested  
* *$lastId* - id of the last message received, used when paging messages  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`getChannelMessages (int $ id, $ int $ accessHash, int $ limit, int $ since, int $ lastId, callable $ onComplete)` - receive channel messages  
* *$id* - channel id  
* *$accessHash* - hash coming from the server in the ‘access_hash’ channel information field  
* *$limit* - how many messages are requested from the server  
* *$since* - timestamp with which messages are requested  
* *$lastId* - id of the last message received, used when paging messages  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`resolveUsername (string $ username, callable $ onComplete)` - resolve of the login for receiving an object with information about the user / chat / channel  
* *$username* - username of the user / chat / channel  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`getByDeepLink (string $ deepLink, callable $ onComplete)` - get channel / chat via diplink  
* *$deepLink* - a short link to the channel / chat, of the form https://t.me/username  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`getAllChats (callable $ onComplete)` - get all chats  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`getInfoByPhone (string $ phone, bool $ withPhoto, bool $ largePhoto, callable $ onComplete)` - get information about the user by phone number  
* *$phone* - user phone number  
* *$withPhoto* - upload avatars  
* *$largePhoto* - upload large avatars  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`getInfoByUsername (string $ userName, bool $ withPhoto, bool $ largePhoto, callable $ onComplete)` - getting user information by username  
* *$username* - username  
* *$withPhoto* - upload avatars  
* *$largePhoto* - upload large avatars  
* *$onComplete* - a callback function, in the input parameter of which comes AnonymousMessage - a message from the server with the requested information  
  
`loadFile (FileModel $ model, callable $ onPictureLoaded)` - download file / image  
* *$model* - a model with information about the location of the file on the network  
* *$onPictureLoaded* - callback function executed when the file is finished loading  
  
`terminate ()` - end a communication session  
 
## StatusWatcherClient
This is client-observer, used to monitor changes in user status on the network. The following methods are available in it:  
  
`login (AuthKey $ authKey [, Proxy $ proxy [, callable $ cb]])` - authorization of the client on the Telegram server.  
* *$authKey* - authorization key received after registering the phone number on the Telegram server  
* *$proxy* - object for using a proxy server for the library  
* *$cb* - callback function that will be executed after authorization.  
  
`isLoggedIn ()` - client authorization check  
  
`pollMessage ()` - a method awaiting receipt of messages from the Telegram server  
  
`onPeriodAvailable ()` - an event that is executed when the user’s availability period on the network changes  
  
`addNumbers (array $ numbers, callable $ onComplete)` - add a phone number  
* *$numbers* - an array with phone numbers  
* *$onComplete* - a callback function that is executed when all numbers are added to the contact list. The input parameter is ImportResult $ result, which contains the results of adding numbers.  
  
`reloadNumbers (array $ numbers, callback $ onComplete)` - "reload" the phone numbers in the contact list - existing ones are deleted, transferred to the method - are added.  
* *$numbers* - an array with phone numbers  
* *$onComplete* - callback function executed after number reload  
  
`delNumbers (array $ numbers, callable $ onComplete)` - delete phone numbers from the contact list  
* *$numbers* - an array with phone numbers  
* *$onComplete* - callback function executed after the completion of number deletion  
  
`addUser (string $ userName, callable $ onComplete)` - add a user to the contact list  
* *$userName* - user login  
* *$onComplete* - callback function executed after adding a user  
  
`delUser (string $ userName, callable $ onComplete)` - delete a user from the contact list  
* *$userName* - user login  
* *$onComplete* - callback function executed after user deletion  
  
`cleanMonitoringBook (callable $ onComplete)` - clear contact list  
* *$onComplete* - callback function called after cleaning  
  
`onMessage (AnonymousMessage $ message)` - event when a message is received from the Telegram server  
* *$message* - message from the server  
  
`onUserOnline (int $ userId, int $ expires)` - event when a user appears on the network  
* *$userId* - user id  
* *$expires* - user time on the network  
  
`onUserOffline (int $ userId, int $ wasOnline)` - event when the user is disconnected from the network  
* *$userId* - user id  
* *$wasOnline* - time elapsed since the user left the network  
  
`onUserHidStatus ($ userId, HiddenStatus $ hiddenStatusState)` - event when the user stealth mode changes  
* *$userId* - user id  
* *$hiddenStatusState* - ???  
  
`onContactsImported (ImportedContacts $ contactsObject)` - event when import of contacts is completed  
* *$contactsObject* - an object with information about imported contacts  
  
`terminate ()` - end a communication session