# ClickSend SMS notifications channel for Laravel 5.5+ and Laravel 6.0+

This package makes it easy to send SMS notifications using the [clicksend.com](//clicksend.com) library with Laravel 5.5+ and 6.0+ releases.  
Uses ClickSend PHP API wrapper [https://github.com/ClickSend/clicksend-php]  
The original version of this repo [https://github.com/vladski/laravel-sms-clicksend] will not install into Laravel 5.5 or higher due to unresolvable dependencies.  
I have created this repo as a ZIP & Drop that will exist directly in your Laravel application code.  
Given time, I may re-create a packaged version that will install into the /vendor/ with composer...


## Contents

- [Installation](#installation)
- [Usage](#usage)
- [Events](#events)
- [Api Client](#api-client)
- [Changelog](#changelog)
- [Testing](#testing)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

Install the package manually:
```bash
Download the ZIP and unzip directly into your Laravel application
```

Add the service provider to `config/app.php`:
```php
...
'providers' => [
    ...
    App\Providers\ClickSendServiceProvider::class,
],
...
```

Add your ClickSend username, api_key and optional default sender sms_from to your `config/services.php`:

```php
...
'clicksend' => [
	'username' => env('CLICKSEND_USERNAME'),
	'api_key'  => env('CLICKSEND_API_KEY'),
	'sms_from' => env('CLICKSEND_SMS_FROM'), // optional
],
...
```

## Usage

This ClickSend service is for sending SMS notifications.
You will need to have the 'target' model prepared correctly - see below. Users are already notifiable.
A Notifications class is already included - you may create alternative versions to suit your needs.
Example:

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Library\Services\ClickSend\ClickSendMessage;
use App\Library\Services\ClickSend\ClickSendChannel;

class ClickSendSms extends Notification
{

    /**
         * An custom value
         * (customizable value)
         *
         * @var $custom1
         */
        protected $custom1;
    
        /**
         * Another custom value
         * (customizable value)
         *
         * @var $custom2
         */
        protected $custom2;
    
        /**
         * Required as the connection is tracked in this class
         * (the absence of this property causes errors)
         *
         * @var $connection
         */
        public $connection;
    
        /**
         * Required by the Queueing classes
         *
         * @var $queue
         */
        public $queue;
    
        /**
         * Required by the Queueing classes
         *
         * @var $delay
         */
        public $delay;
    
        /**
         * Create a notification instance.
         * 
         * (This $input is passed when you instantiate this class in your Controller's call to send the notification - pass whatever you need)
         * @param array $input (
         */
        public function __construct($input)
        {
            $this->custom1 = $input[0];
            $this->custom2 = $input[1];
        }
    
        /**
         * @param $notifiable
         *
         * @return array
         */
        public function via($notifiable)
        {
            return [ClickSendChannel::class];
        }
    
        /**
         * Generate the ClickSend message
         * This method is called from \App\Library\Services\ClickSend\ClickSendChannel
         * You need to choose the $message creation that works best for you.
         *
         * The $notifiable VAR refers back to the Model that you are using to initialise the notification
         * @param $notifiable
         *
         * @return ClickSendMessage
         */
        public function toClickSend($notifiable)
        {
            // statically create message object:
            $message = ClickSendMessage::create("SMS test to user #{$notifiable->id} with token {$this->token} by ClickSend");
    
            // OR instantiate:
            $message = new ClickSendMessage("Your AdultMuse ad: {$this->host}/latest/view/{$notifiable->user_key}/{$notifiable->post_key} with ID {$this->adID} from AdultMuse");
    
            // available methods:
            $message->content("SMS test to user #{$notifiable->id} with token {$this->token} by ClickSend");
            $message->from('+6112345678'); // override sms_from from config
    
            return (new ClicksendSmsMessage())
                ->content("Thank you! You successfully paid for your Order #" . $notifiable-&gt;order_id);
    
            return $message;
        }
}
```

In notifiable model (User), include method `routeNotificationForClickSend()` that returns recipient mobile number:

```php
...
public function routeNotificationForClickSend()
{
    return $this->phone;
}
...
```
Don't forget to add the Notifiable clause if your are using custom Models:
```php
...
class Post extends Model
{
    use Notifiable;
}
...
```

From controller then send notification standard way:
```php

$user = User::find(1);

try {
	$user->notify(new ClickSendTest('ABC123'));
}
catch (\Exception $e) {
	// do something when error
	return $e->getMessage();
}
```
or
```php
...
$post = Post::where('id', '=', $id)->first()
$array = array($insertID, $host);
try {
    $post->notify(new ClickSendSms($array));
}
catch (\Exception $e) {
	// do something when error
	return $e->getMessage();
}

...
```
## Events
Following events are triggered by Notification. By default:
- Illuminate\Notifications\Events\NotificationSending
- Illuminate\Notifications\Events\NotificationSent

and this channel triggers one when submission fails for any reason:
- Illuminate\Notifications\Events\NotificationFailed

To listen to those events create listener classes in `app/Listeners` folder e.g. to log failed SMS:

```php

namespace App\Listeners;
	
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use NotificationChannels\ClickSend\ClickSendChannel;
	
class NotificationFailedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Notification failed event handler
     *
     * @param  NotificationFailed  $event
     * @return void
     */
    public function handle(NotificationFailed $event)
    {
        // Handle fail event for ClickSend
        //
        if($event->channel == ClickSendChannel::class) {
	
            echo 'failed'; dump($event);
            
            $logData = [
            	'notifiable'    => $event->notifiable->id,
            	'notification'  => get_class($event->notification),
            	'channel'       => $event->channel,
            	'data'      => $event->data
            	];
            	
            Log::error('Notification Failed', $logData);
         }
         // ... handle other channels ...
    }
}
```
Then register listeners in `app/Providers/EventServiceProvider.php`
```php
...
protected $listen = [

	'Illuminate\Notifications\Events\NotificationFailed' => [
		'App\Listeners\NotificationFailedListener',
	],

	'Illuminate\Notifications\Events\NotificationSent' => [
		'App\Listeners\NotificationSentListener',
	],

	'Illuminate\Notifications\Events\NotificationSending' => [
		'App\Listeners\NotificationSendingListener',
	],
];
...
```

## API Client

To access the rest of ClickSend API you can get client from ClickSendApi:
```php
$client = app(ClickSendApi::class)->getClient();
	
// then get for eaxample yor ClickSend account details:
$account =  $client->getAccount()->getAccount();
	
// or list of countries:
$countries =  $client->getCountries()->getCountries();

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

Incomplete
``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [vladski](https://github.com/vladski)
- [gilbert-rehling](https://github.com/gilbert-rehling)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
