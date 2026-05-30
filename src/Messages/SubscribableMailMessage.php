<?php

namespace YlsIdeas\SubscribableNotifications\Messages;

use Illuminate\Notifications\Messages\MailMessage;
use Symfony\Component\Mime\Email;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;

class SubscribableMailMessage extends MailMessage
{
    public $markdown = 'subscriber::html';

    public static function via(CanUnsubscribe $notifiable, object $notification): static
    {
        $message = new static();

        $list = $notification instanceof AppliesToMailingList
            ? $notification->usesMailingList()
            : null;
        $listValue = $list instanceof \BackedEnum ? $list->value : $list;

        if ($listValue !== null) {
            $message->viewData['unsubscribeLink'] = $notifiable->unsubscribeLink($listValue);
        }
        $message->viewData['unsubscribeLinkForAll'] = $notifiable->unsubscribeLink();

        $unsubscribeUrl = $notifiable->unsubscribeLink($listValue);

        return $message->withSymfonyMessage(function (Email $email) use ($unsubscribeUrl) {
            $email->getHeaders()->addTextHeader('List-Unsubscribe', sprintf('<%s>', $unsubscribeUrl));
            $email->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        });
    }
}
