# api.box.com
Api storage box.com v2.0

# Installation
You can install the package via composer:
```
composer require varik-kos/o-auth-box-com
```


# Usage
The first thing you need to do is get an authorization token at Box.com 
https://app.box.com/developers/console
You'll find more info at the Box Developer Blog https://developer.box.com/reference

```php
use VarikKos\BoxComApi\Client

$authToken = '**************************'
//folder_id - The ID of the folder object
$folder = 'folders/folder_id/items';

$client = new Client($authToken);
$client->getFolderItems($folder);
```

# PHP 7
