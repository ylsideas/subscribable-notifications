<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

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
    public $userModel = '\App\Models\User';
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
     * @param mixed $user
     * @param string $mailingList
     */
    public function unsubscribeFromMailingList($user, string $mailingList)
    {
        call_user_func($this->onUnsubscribeFromMailingList, $user, $mailingList);
    }

    /**
     * @param mixed $user
     */
    public function unsubscribeFromAllMailingLists($user)
    {
        call_user_func($this->onUnsubscribeFromAllMailingLists, $user);
    }

    /**
     * @param mixed $user
     * @param string|null $mailingList
     * @return Response
     */
    public function complete($user, ?string $mailingList = null)
    {
        return call_user_func($this->onCompletion, $user, $mailingList);
    }

    /**
     * @param mixed $user
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

    protected function parseHandler(string|callable$handler): callable
    {
        if (is_string($handler)) {
            $parsed = Str::parseCallback($handler);
            $parsed[0] = $this->app->make($parsed[0]);

            return $parsed;
        }

        return $handler;
    }
}
