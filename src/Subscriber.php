<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Subscriber
{
    /**
     * @var string
     */
    public $uri = 'unsubscribe/{subscriber}/{mailingList?}';
    /**
     * @var string
     */
    public $hander = '\YlsIdeas\SubscribableNotifications\Controllers\UnsubscribeController';
    /**
     * @var string
     */
    public $routeName = 'unsubscribe';
    /**
     * @var string
     */
    public $userModel = '\App\User';
    /**
     * @var callable
     */
    protected $onUnsubscribeFromMailingList;
    /**
     * @var callable
     */
    protected $onUnsubscribeFromAllMailingLists;
    /**
     * @var callable
     */
    protected $onCompletion;
    /**
     * @var callable
     */
    protected $onCheckSubscriptionStatusForMailingLists;
    /**
     * @var callable
     */
    protected $onCheckSubscriptionStatusForAllMailingLists;

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function routes($router = null)
    {
        $router = $router ?? $this->app->make('router');
        $router->get(
            $this->uri,
            $this->hander
        )
            ->name($this->routeName);
    }

    /**
     * @return string
     */
    public function routeName()
    {
        return $this->routeName;
    }

    /**
     * @param string|null $model
     * @return string|null
     */
    public function userModel(?string $model = null)
    {
        if ($model) {
            $this->userModel = $model;

            return null;
        }

        return $this->userModel;
    }

    /**
     * @param string|callable $handler
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onUnsubscribeFromMailingList($handler)
    {
        $this->onUnsubscribeFromMailingList = $this->parseHandler($handler);
    }

    /**
     * @param string|callable $handler
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onUnsubscribeFromAllMailingLists($handler)
    {
        $this->onUnsubscribeFromAllMailingLists = $this->parseHandler($handler);
    }

    /**
     * @param string|callable $handler
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onCompletion($handler)
    {
        $this->onCompletion = $this->parseHandler($handler);
    }

    /**
     * @param string|callable $handler
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onCheckSubscriptionStatusOfAllMailingLists($handler)
    {
        $this->onCheckSubscriptionStatusForAllMailingLists = $this->parseHandler($handler);
    }

    /**
     * @param string|callable $handler
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onCheckSubscriptionStatusOfMailingList($handler)
    {
        $this->onCheckSubscriptionStatusForMailingLists = $this->parseHandler($handler);
    }

    /**
     * @param $user
     * @param string $mailingList
     */
    public function unsubscribeFromMailingList($user, string $mailingList)
    {
        call_user_func($this->onUnsubscribeFromMailingList, $user, $mailingList);
    }

    /**
     * @param $user
     */
    public function unsubscribeFromAllMailingLists($user)
    {
        call_user_func($this->onUnsubscribeFromAllMailingLists, $user);
    }

    /**
     * @param $user
     * @param string|null $mailingList
     * @return Response
     */
    public function complete($user, ?string $mailingList = null)
    {
        return call_user_func($this->onCompletion, $user, $mailingList);
    }

    /**
     * @param $user
     * @param string|null $mailingList
     * @return bool
     */
    public function checkSubscriptionStatus($user, ?string $mailingList = null)
    {
        if ($mailingList !== null) {
            return (bool) call_user_func($this->onCheckSubscriptionStatusForAllMailingLists, $user) &&
                (bool) call_user_func($this->onCheckSubscriptionStatusForMailingLists, $user, $mailingList);
        }

        return (bool) call_user_func($this->onCheckSubscriptionStatusForAllMailingLists, $user);
    }

    /**
     * @param string|callable $handler
     * @return callable
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function parseHandler($handler)
    {
        if (is_string($handler)) {
            $parsed = Str::parseCallback($handler);
            $parsed[0] = $this->app->make($parsed[0]);

            return $parsed;
        } elseif (is_callable($handler)) {
            return $handler;
        }

        throw new InvalidArgumentException('Handler argument must be either a string or callable.');
    }
}
