<?php
/**
 * Defines a namespace for the class.
 */
namespace App\Library\Services\ClickSend;

/**
 * @uses
 */
use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Events\NotificationFailed;

/**
 * Class ClickSendChannel
 *
 * @package App\Library\Services\ClickSend
 */
class ClickSendChannel
{
    /** @var \NotificationChannels\ClickSend\ClickSendApi */
    protected $client;

    /** @var Dispatcher */
    protected $events;

    /**
     * ClickSendChannel constructor.
     *
     * @param ClickSendApi $client
     * @param Dispatcher $events
     */
    public function __construct(ClickSendApi $client, Dispatcher $events)
    {
        $this->client = $client;
        $this->events = $events;
    }

    /**
     * @param $notifiable
     * @param Notification $notification
     *
     * @return array
     * @throws \Exception
     */
    public function send($notifiable, Notification $notification)
    {
        $to = $notifiable->routeNotificationForClicksend();

        $message = $notification->toClickSend($notifiable);

        // always return object
        if (is_string($message)) $message = new ClickSendMessage($message);

        // array [success, message, data]
        $result = $this->client->sendSms($message->from, $to, $message->content); //dd($result);

        if (empty($result['success']))
        {
            $this->events->fire(
                new NotificationFailed($notifiable, $notification, get_class($this), $result)
            );

            // by throwing exception NotificationSent event is not triggered and we trigger NotificationFailed above instead
            throw new \Exception('Notification failed '.$result['message']);
        }

        return $result;
    }
}
