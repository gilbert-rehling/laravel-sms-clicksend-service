<?php
/**
 * Defines a namespace for the class.
 */
namespace App\Exceptions\ClickSend;

/**
 * @uses
 */
use Exception;
use DomainException;

/**
 * Class CouldNotSendNotification
 *
 * @package App\Exceptions\ClickSend
 */
class CouldNotSendNotification extends Exception
{
    /**
     * Thrown when content length is greater than 800 characters.
     *
     * @return static
     */
    public static function contentLengthLimitExceeded()
    {
        return new static(
            'Notification was not sent. Content length may not be greater than 800 characters.'
        );
    }

    /**
     * Thrown when message status is not SUCCESS
     *
     * @param  DomainException  $exception
     *
     * @return static
     */
    public static function clicksendRespondedWithAnError(DomainException $exception)
    {
        return new static(
            "Notification Error: {$exception->getMessage()}"
        );
    }

    /**
     * Thrown when we're unable to communicate with Clicksend.com
     *
     * @param  Exception  $exception
     *
     * @return static
     */
    public static function couldNotCommunicateWithClicksend(Exception $exception)
    {
        return new static("Notification Gateway Error: {$exception->getReason()} [{$exception->getCode()}]");
    }
}
