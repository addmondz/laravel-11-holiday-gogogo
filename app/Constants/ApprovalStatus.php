<?php

namespace App\Constants;

class ApprovalStatus
{
    public const PENDING_PAYMENT = 0;
    public const PAYMENT_COMPLETED = 1;
    public const BOOKING_CONFIRMED = 2;
    public const BOOKING_REJECTED = 3; // pending refund
    public const REFUNDED = 4;

    public const STATUS_TITLE = [
        self::PENDING_PAYMENT => 'Pending Payment',
        self::PAYMENT_COMPLETED => 'Payment Completed',
        self::BOOKING_CONFIRMED => 'Booking Confirmed',
        self::BOOKING_REJECTED => 'Booking Rejected',
        self::REFUNDED => 'Refunded',
    ];

    public const ALL_STATUS = [
        self::PENDING_PAYMENT,
        self::PAYMENT_COMPLETED,
        self::BOOKING_CONFIRMED,
        self::BOOKING_REJECTED,
        self::REFUNDED,
    ];
}
