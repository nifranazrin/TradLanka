<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SellerDashboardNotification extends Notification
{
    use Queueable;

    protected string $type;
    protected string $message;
    protected ?int $actionId;
    protected ?string $route;

    /**
     * @param string      $type      product | order | inquiry | chat
     * @param string      $message   Message shown to seller
     * @param int|null    $actionId  Related model ID
     * @param string|null $route     Route name for redirect
     */
    public function __construct(
        string $type,
        string $message,
        int $actionId = null,
        string $route = null
    ) {
        $this->type     = $type;
        $this->message  = $message;
        $this->actionId = $actionId;
        $this->route    = $route;
    }

    /**
     * Notification channels
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Data stored in notifications table
     */
    public function toArray($notifiable): array
    {
        return [
            'type'      => $this->type,          // product / order / inquiry / chat
            'message'   => $this->message,
            'action_id' => $this->actionId,
            'route'     => $this->route,         // e.g. seller.products.index
            'title'     => ucfirst($this->type) . ' Notification',
        ];
    }
}
