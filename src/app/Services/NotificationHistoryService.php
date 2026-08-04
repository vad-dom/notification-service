<?php

namespace App\Services;

use App\Models\Recipient;
use Illuminate\Contracts\Pagination\CursorPaginator;

readonly class NotificationHistoryService
{
    private const int PER_PAGE = 15;

    public function paginateForRecipient(Recipient $recipient): CursorPaginator
    {
        return $recipient->notifications()
            ->with('batch')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(self::PER_PAGE);
    }
}
