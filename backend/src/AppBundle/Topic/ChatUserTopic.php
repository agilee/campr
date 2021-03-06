<?php

namespace AppBundle\Topic;

use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use AppBundle\Services\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class ChatUserTopic implements TopicInterface
{
    private $chatService;

    private $clientManipulator;

    private $em;

    public function __construct(
        ChatService $chatService,
        ClientManipulatorInterface $clientManipulator,
        EntityManagerInterface $em
    ) {
        $this->chatService = $chatService;
        $this->clientManipulator = $clientManipulator;
        $this->em = $em;
    }

    /**
     * This will receive any Subscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic               $topic
     * @param WampRequest         $request
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        //this will broadcast the message to ALL subscribers of this topic.
        $topic->broadcast(['message' => $connection->resourceId.' has joined '.$topic->getId()]);
    }

    /**
     * This will receive any UnSubscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic               $topic
     * @param WampRequest         $request
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        //this will broadcast the message to ALL subscribers of this topic.
        $topic->broadcast(['message' => $connection->resourceId.' has left '.$topic->getId()]);
    }

    /**
     * This will receive any Publish requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic               $topic
     * @param WampRequest         $request
     * @param $event
     * @param array $exclude
     * @param array $eligible
     *
     * @return mixed|void
     */
    public function onPublish(
        ConnectionInterface $connection,
        Topic $topic,
        WampRequest $request,
        $event,
        array $exclude,
        array $eligible
    ) {
        $fromId = $this->clientManipulator->getClient($connection)->getId();
        $user = $this->em->getReference(User::class, $fromId);
        $project = $this->em->getReference(Project::class, $event['data']['project']);
        $to = $this->em->getReference(User::class, $event['data']['chat']);
        if ($user != $to) {
            $message = $this->chatService->createMessage($project, $event['data']['body'], $user, null, $to);
            $event['message'] = $message->getBody();
            $event['time'] = $message->getCreatedAt()->format('H:i');
            $event['username'] = $user->getUsername();
            $event['from_id'] = $user->getId();
        }

        $topic->broadcast([
            'message' => $event,
        ]);
    }

    /**
     * Like RPC is will use to prefix the channel.
     *
     * @return string-
     */
    public function getName()
    {
        return 'chat.user.topic';
    }
}
