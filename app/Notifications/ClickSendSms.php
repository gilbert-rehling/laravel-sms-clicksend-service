<?php
/**
 * Defines a namespace for the class.
 */
namespace App\Notifications;

/**
 * @uses
 */
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Library\Services\ClickSend\ClickSendMessage;
use App\Library\Services\ClickSend\ClickSendChannel;

/**
 * Class ClickSendSms
 *
 * @package App\Notifications
 */
class ClickSendSms extends Notification implements ShouldQueue
{
    /**
     * An item ID value provided during instantiation in the Controller
     * (customizable value)
     *
     * @var $adID
     */
    protected $adID;

    /**
     * Application URL value provided during the instantiation in the Controller
     * (customizable value)
     *
     * @var $host
     */
    protected $host;

    /**
     * Required as the connection is tracked back to here
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
     * (This is passed when you Create this class in your Controller's call to send the notification - pass whatever you need)
     *
     * @param array $input
     */
    public function __construct($input)
    {
        $this->adID = $input[0];
        $this->host = $input[1];
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
     *
     * @param $notifiable
     *
     * @return ClickSendMessage
     */
    public function toClickSend($notifiable)
    {
        // statically create message object:
        // $message = ClickSendMessage::create("SMS test to user #{$notifiable->id} with token {$this->token} by ClickSend");

        // OR instantiate:
        $message = new ClickSendMessage("Your AdultMuse ad: {$this->host}/latest/view/{$notifiable->user_key}/{$notifiable->post_key} with ID {$this->adID} from AdultMuse");

        // available methods:
//        $message->content("SMS test to user #{$notifiable->id} with token {$this->token} by ClickSend");
//        $message->from('+6112345678'); // override sms_from from config

//        return (new ClicksendSmsMessage())
//            ->content("Thank you! You successfully paid for your Order #" . $notifiable-&gt;order_id);

        return $message;
    }
}
