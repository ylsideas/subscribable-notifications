<?php

namespace YlsIdeas\SubscribableNotifications\Concerns;

use Symfony\Component\Mime\Email;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;

trait SubscribableNotification
{
    public static function via(
        CanUnsubscribe $notifiable,
        object $notification,
        string $template = 'subscriber::html'
    ): static {
        $message = new static();
        $message->markdown = $template;

        $list = $notification instanceof AppliesToMailingList
            ? $notification->usesMailingList()
            : null;
        $listValue = $list instanceof \BackedEnum ? $list->value : $list;

        $unsubscribeUrl = $notifiable->unsubscribeLink($listValue);

        if ($listValue !== null) {
            $message->viewData['unsubscribeLink'] = $unsubscribeUrl;
        }
        $message->viewData['unsubscribeLinkForAll'] = $notifiable->unsubscribeLink();

        return $message->withSymfonyMessage(function (Email $email) use ($unsubscribeUrl) {
            $email->getHeaders()->addTextHeader('List-Unsubscribe', sprintf('<%s>', $unsubscribeUrl));
            $email->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        });
    }
}
