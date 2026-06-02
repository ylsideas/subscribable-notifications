<?php

namespace YlsIdeas\SubscribableNotifications\Channels;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Contracts\CheckNotifiableSubscriptionStatus;
use YlsIdeas\SubscribableNotifications\Contracts\CheckSubscriptionStatusBeforeSendingNotifications;

/**
 * @deprecated v1.x will be removed in v2.0. Automatic unsubscribe-link injection via a custom
 *             MailChannel is replaced by explicit opt-in: return SubscribableMailMessage::via($notifiable, $this)
 *             from your notification's toMail() method instead.
 *             See the upgrade guide: UPGRADE.md
 */
class SubscriberMailChannel extends MailChannel
{
    /**
     * The mailer implementation.
     *
     * @var \Illuminate\Contracts\Mail\Factory
     */
    protected $mailer;

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($notifiable, Notification $notification)
    {
        trigger_error(
            'SubscriberMailChannel is deprecated and will be removed in v2.0.'
                . ' Return SubscribableMailMessage::via($notifiable, $this) from your notification\'s'
                . ' toMail() method to opt in to unsubscribe-link injection explicitly.',
            E_USER_DEPRECATED
        );

        // Check if the user would want the mail
        if ($notifiable instanceof CheckSubscriptionStatusBeforeSendingNotifications &&
            $notification instanceof CheckNotifiableSubscriptionStatus &&
            $notification->checkMailSubscriptionStatus() &&
            ! $notifiable->mailSubscriptionStatus($notification)) {
            return null;
        }

        if (method_exists($notification, 'toMail')) {
            $message = $notification->toMail($notifiable);
        } else {
            throw new \RuntimeException('Notification does not support sending mail');
        }

        // Inject unsubscribe links for rendering in the view
        if ($notifiable instanceof CanUnsubscribe && $message instanceof MailMessage) {
            if ($notification instanceof AppliesToMailingList) {
                $message->viewData['unsubscribeLink'] = $notifiable->unsubscribeLink(
                    $notification->usesMailingList()
                );
            }
            $message->viewData['unsubscribeLinkForAll'] = $notifiable->unsubscribeLink();
        }

        if (! $notifiable->routeNotificationFor('mail', $notification) &&
            ! $message instanceof Mailable) {
            return null;
        }

        if ($message instanceof Mailable) {
            $message->send($this->mailer);

            return null;
        }

        return $this->mailer->mailer($message->mailer ?? null)->send(
            $this->buildView($message),
            array_merge($message->data(), $this->additionalMessageData($notification)),
            $this->messageBuilder($notifiable, $notification, $message)
        );
    }

    /**
     * Build the mail message.
     *
     * @param \Illuminate\Mail\Message $mailMessage
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @param \Illuminate\Notifications\Messages\MailMessage $message
     *
     * @return void
     */
    protected function buildMessage($mailMessage, $notifiable, $notification, $message)
    {
        parent::buildMessage($mailMessage, $notifiable, $notification, $message);

        if ($notifiable instanceof CanUnsubscribe) {
            $mailMessage->getHeaders()->addTextHeader(
                'List-Unsubscribe',
                sprintf('<%s>', $notifiable->unsubscribeLink(
                    $notification instanceof AppliesToMailingList
                        ? $notification->usesMailingList()
                        : null
                ))
            );
        }
    }

    /**
     * Build the notification's view.
     *
     * @param  \Illuminate\Notifications\Messages\MailMessage  $message
     * @return string|array
     */
    protected function buildView($message)
    {
        if ($message->view) {
            return $message->view;
        }

        return [
            'html' => $this->markdown->render('subscriber::html', $message->data()),
            'text' => $this->markdown->renderText('subscriber::text', $message->data()),
        ];
    }
}
