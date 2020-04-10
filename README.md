# Zoom PHP API

A simple PHP wrapper for the Zoom API

[![Latest Stable Version](http://img.shields.io/packagist/v/espresso-dev/zoom-php.svg?style=flat)](https://packagist.org/packages/espresso-dev/zoom-php)
[![License](https://img.shields.io/packagist/l/espresso-dev/zoom-php.svg?style=flat)](https://packagist.org/packages/espresso-dev/zoom-php)
[![Total Downloads](http://img.shields.io/packagist/dt/espresso-dev/zoom-php.svg?style=flat)](https://packagist.org/packages/espresso-dev/zoom-php)

> [Composer](#installation) package available.

## Requirements

- PHP 7 or higher
- cURL
- Zoom Developer Account
- Zoom App

## Get started

To use the [Zoom API](https://marketplace.zoom.us/docs/guides/tools-resources/zoom-apis), you will need to register a Zoom app. Follow the [Create an OAuth App guide](https://marketplace.zoom.us/docs/guides/getting-started/app-types/create-oauth-app).

### Installation

I strongly advice using [Composer](https://getcomposer.org) to keep updates as smooth as possible.

```
$ composer require espresso-dev/zoom-php
```

### Initialize the class

```php
use EspressoDev\Zoom\Zoom;

$zoom = new Zoom([
    'appId' => 'YOUR_APP_ID',
    'appSecret' => 'YOUR_APP_SECRET',
    'redirectUri' => 'YOUR_APP_REDIRECT_URI'
]);

echo "<a href='{$zoom->getLoginUrl()}'>Login with Zoom</a>";
```

### Authenticate user (OAuth2)

```php
// Get the OAuth callback code
$code = $_GET['code'];

// Get the access token (valid for 1 hour) and refresh token
$token = $zoom->getOAuthToken($code);

echo 'Your token is: ' . $token->access_token;
echo 'Your refresh token is: ' . $token->refresh_token;
```

### Get users scheduled meetings

```php
// Set user access token
$zoom->setAccessToken($token);

// Get the users scheduled meetins
$meetings = $zoom->getUserMeetings('me', 'scheduled');

echo '<pre>';
print_r($meetings);
echo '<pre>';
```

**All methods return the API data as `json_decode()` - so you can directly access the data.**

## Available methods

### Setup Zoom

`new Zoom(<array>/<string>);`

`array` if you want to perform oAuth:

```php
new Zoom([
    'appId' => 'YOUR_APP_ID',
    'appSecret' => 'YOUR_APP_SECRET',
    'redirectUri' => 'YOUR_APP_REDIRECT_URI'
]);
```

`string` once you have a token and just want to return *read-only* data:

```php
new Zoom('ACCESS_TOKEN');
```

### Get login URL

`getLoginUrl(<string>)`

```php
getLoginUrl(
    'state'
);
```

### Get OAuth token (Short lived valid for 1 hour)

`getOAuthToken($code)`

### Refresh access token for another 1 hour and get updated refresh token

`refreshToken($refreshToken)`

### Set / Get access token

- Set the access token, for further method calls: `setAccessToken($token)`
- Get the access token, if you want to store it for later usage: `getAccessToken()`

### User methods

See [Zoom API Documentation](https://marketplace.zoom.us/docs/api-reference/zoom-api) for more information about each method.

**Authenticated methods**

- `getUserMeetings(<$id>, <$type>, <$page_size>, <$page_number>)`
- `getUserMeetings(<$id>, <$page_size>, <$page_number>)`
