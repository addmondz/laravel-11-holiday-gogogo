<?php

namespace App\Constants;

class ApprovalStatus
{
    public const PENDING_APPROVAL = 0;
    public const APPROVED = 1;
    public const REJECTED = 2;
    public const PAYMENT_COMPLETED = 3;
    public const REFUNDED = 4;

    public const STATUS_TITLE = [
        self::PENDING_APPROVAL => 'Pending Approval',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected',
        self::PAYMENT_COMPLETED => 'Payment Completed',
        self::REFUNDED => 'Refunded',
    ];

    public const ALL_STATUS = [
        self::PENDING_APPROVAL,
        self::APPROVED,
        self::REJECTED,
        self::PAYMENT_COMPLETED,
        self::REFUNDED,
    ];
}
