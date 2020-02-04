<?php

namespace NotificationChannel\ClickSend\Tests;

use Mockery as M;
use App\Notifications\ClickSendSms;
use App\Library\Services\ClickSend\ClickSendApi;
use App\Library\Services\ClickSend\ClickSendChannel;
use App\Library\Services\ClickSend\ClickSendMessage;
use App\Exceptions\ClickSend\CouldNotSendNotification;

class ClickSendChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClickSendApi
     */
    private $smsc;

    /**
     * @var ClickSendMessage
     */
    private $message;

    /**
     * @var ClickSendChannel
     */
    private $channel;

    public function setUp()
    {
        parent::setUp();

        $this->smsc = M::mock(ClickSendApi::class, ['test', 'test', 'John_Doe']);
        $this->channel = new ClickSendChannel($this->smsc);
        $this->message = M::mock(ClickSendMessage::class);
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    /** @test */
    public function it_can_send_a_notification()
    {
        $this->smsc->shouldReceive('send')->once()
            ->with(
                [
                    'phones'  => '+1234567890',
                    'mes'     => 'hello',
                    'sender'  => 'John_Doe',
                ]
            );

        $this->channel->send(new TestNotifiable(), new TestNotification());
    }

    /** @test */
    public function it_does_not_send_a_message_when_to_missed()
    {
        $this->expectException(CouldNotSendNotification::class);

        $this->channel->send(
            new TestNotifiableWithoutRouteNotificationForSmscru(), new TestNotification()
        );
    }
}

class TestNotifiable
{
    public function routeNotificationFor()
    {
        return '+1234567890';
    }
}

class TestNotifiableWithoutRouteNotificationForSmscru extends TestNotifiable
{
    public function routeNotificationFor()
    {
        return false;
    }
}

class TestNotification extends Notification
{
    public function toClickSend()
    {
        return ClickSendMessage::create('hello')->from('John_Doe');
    }
}
