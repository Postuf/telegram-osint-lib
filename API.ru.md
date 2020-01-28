## InfoClient
 Класс предназначен для активного получение информации с сервера Telegram и имеет следующие методы:

`login(AuthKey $authKey[, Proxy $proxy[, callable $cb]])` - авторизация клиента на сервере Telegram.  
* $authKey - ключ авторизации, полученный после регистрации телефонного номера на сервере Telegram.  
* $proxy - объект для использования прокси-сервера для работы библиотеки  
* $cb - callback-функция, которая будет выполнена после авторизации.   

`isLoggedIn()` - проверка авторизации клиента  

`pollMessage()` - метод ожидающий получения сообщений от сервера Telegram

`getChatMembers(int $id, callable $onComplete)` - получение участников чата.  
* $id - id чата  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`getChannelMembers(int $id, int $accessHash, callable $onComplete)` - получение участников канала  
* $id - id канала  
* $accessHash - хэш, приходящий от сервера в поле ‘access_hash’ информации о канале  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`getFullChannel(int $id, int $accessHash, callable $onComplete)` - ???  

`getChatMessages(int $id, int $limit, int $since, int $lastId, callable $onComplete)` - получание сообщений из чата с возможностью фильтрации по времени и количеству.  
* $id - id чата   
* $limit - какое количество сообщений запрашивается у сервера  
* $since - временная метка, с которой запрашиваются сообщения  
* $lastId - id последнего полученного сообщения, используется при постраничном получении сообщений  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`getChannelMessages(int $id, $int $accessHash, int $limit, int $since, int $lastId, callable $onComplete)` - получение сообщений канала  
* $id - id канала  
* $accessHash - хэш, приходящий от сервера в поле ‘access_hash’ информации о канале   
* $limit - какое количество сообщений запрашивается у сервера  
* $since - временная метка, с которой запрашиваются сообщения  
* $lastId - id последнего полученного сообщения, используется при постраничном получении сообщений  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`resolveUsername(string $username, callable $onComplete)` - «разрешение» логина для получения объекта с информацией о пользователе/чате/канале  
* $username - логин пользователя/чата/канала  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`getByDeepLink(string $deepLink, callable $onComplete)` - получение канала/чата по диплинку  
* $deepLink - короткая ссылка на канал/чат, вида https://t.me/username  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`getAllChats(callable $onComplete)` - получить все чаты  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`getInfoByPhone(string $phone, bool $withPhoto, bool $largePhoto, callable $onComplete)` - получить информацию о пользователе по номеру телефона  
* $phone - номер телефона пользователя  
* $withPhoto - загружать аватарки  
* $largePhoto - загружать аватарки большого размера  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`getInfoByUsername(string $userName, bool $withPhoto, bool $largePhoto, callable $onComplete)` - получение информации о пользователе по юзернейму  
* $username - логин пользователя  
* $withPhoto - загружать аватарки  
* $largePhoto - загружать аватарки большого размера  
* $onComplete - callback-функция, во входяем параметре которой приходит AnonymousMessage - сообщение от сервера с запрошенной информацией 

`loadFile(FileModel $model, callable $onPictureLoaded)` - скачиваение файла/изображения  
* $model - модель с информацией о расположении файла в сети  
* $onPictureLoaded - callback-функция, выполняемая при окончании загрузки файла 

`terminate()` - завершить сеанс связи

## StatusWatcherClient
 Клиент-наблюдатель, используется для мониторинга изменения статуса пользователя в сети. В нем доступные следующие методы:

`login(AuthKey $authKey[, Proxy $proxy[, callable $cb]])` - авторизация клиента на сервере Telegram.  
* $authKey - ключ авторизации, полученный после регистрации телефонного номера на сервере Telegram  
* $proxy - объект для использования прокси-сервера для работы библиотеки  
* $cb - callback-функция, которая будет выполнена после авторизации.  

`isLoggedIn()` - проверка авторизации клиента

`pollMessage()` - метод ожидающий получения сообщений от сервера Telegram

`onPeriodAvailable()` - событие, выполняющееся при изменении периода доступности пользователя в сети

`addNumbers(array $numbers, callable $onComplete)` - добавление номера телефона  
* $numbers - массив с номерами телефонов  
* $onComplete - callback-функция, выполняемая при добавлении всех номеров в контакт-лист. На вход поступает параметр ImportResult $result, содержащий результаты добавления номеров. 

`reloadNumbers(array $numbers, callback $onComplete)` - «перезагрузка» номеров телефонов в контакт-листе - существующие удаляются, переданные в метод - добвляются.  
* $numbers - массив с номерами телефонов  
* $onComplete - callback-функция, выполняемая после перезагрузки номеров 

`delNumbers(array $numbers, callable $onComplete)` - удаление номеров телефонов из контакт-листа  
* $numbers - массив с номерами телефонов  
* $onComplete - callback-функция, выполняемая после завершение удаления номеров 

`addUser(string $userName, callable $onComplete)` - добавление пользователя в контакт-лист  
* $userName - логин пользователя  
* $onComplete - callback-функция, выполняемая после добавления пользователя 

`delUser(string $userName, callable $onComplete)` - удаление пользователя из контакт-листа  
* $userName - логин пользователя  
* $onComplete - callback-функция, выполняемая после удаления пользователя 

`cleanMonitoringBook(callable $onComplete)` - очистить контакт-лист  
* $onComplete - callback-функция, вызываемая после очистки  

`onMessage(AnonymousMessage $message)` - событие при получении сообщения от сервера Telegram  
* $message - сообщение от сервера 

`onUserOnline(int $userId, int $expires)` - событие при появлении пользователя в сети  
* $userId - id пользователя,  
* $expires - время пользователя в сети 

`onUserOffline(int $userId, int $wasOnline)` - событие при отключения пользователя от сети  
* $userId - id пользователя  
* $wasOnline - время, прошедшее с момента выхода пользователя из сети 

`onUserHidStatus($userId, HiddenStatus $hiddenStatusState)` - событие при смене режима скрытности пользователя  
* $userId - id пользователя  
* $hiddenStatusState - ??? 

`onContactsImported(ImportedContacts $contactsObject)` - событие при завершении импорта контактов  
* $contactsObject - объект с информацией об импортированных контактах 

`terminate()` - завершить сеанс связи