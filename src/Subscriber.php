<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;

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

    public function routes($router = null)
    {
        $router = $router ?? Route::getFacadeRoot();
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
     * @param callable $handler
     */
    public function onUnsubscribeFromMailingList(callable $handler)
    {
        $this->onUnsubscribeFromMailingList = $this->parseHandler($handler);
    }

    /**
     * @param callable $handler
     */
    public function onUnsubscribeFromAllMailingLists(callable $handler)
    {
        $this->onUnsubscribeFromAllMailingLists = $this->parseHandler($handler);
    }

    /**
     * @param callable $handler
     */
    public function onCompletion(callable $handler)
    {
        $this->onCompletion = $this->parseHandler($handler);
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
     * @param User $user
     * @param string|null $mailingList
     * @return Response
     */
    public function complete($user, ?string $mailingList = null)
    {
        return call_user_func($this->onCompletion, $user, $mailingList);
    }

    /**
     * @param callable $handler
     * @return callable
     */
    protected function parseHandler(callable $handler)
    {
        return is_string($handler) ? Str::parseCallback($handler) : $handler;
    }
}
