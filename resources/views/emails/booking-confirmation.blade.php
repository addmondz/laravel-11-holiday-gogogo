<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f5f7; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #eef0fb; padding: 30px 40px; text-align: center;">
                            <h1 style="color: #1f2937; margin: 0 0 10px 0; font-size: 24px; font-weight: 700;">Booking Created!</h1>
                            <p style="color: #4b5563; margin: 0 0 6px 0; font-size: 14px;">Your booking has been submitted and is currently <span style="color: #d97706; font-weight: 600;">pending</span>.</p>
                            <p style="color: #6b7280; margin: 0; font-size: 13px;">A travel consultant will contact you shortly to assist with your booking.</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px 40px;">
                            @php
                                $nights = $booking->start_date->diffInDays($booking->end_date);
                                $days = $nights + 1;
                            @endphp

                            <!-- Two Column Layout -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td valign="top" width="260">
                                        <!-- LEFT COLUMN -->

                                        <!-- Card 1: Booking Reference -->
                                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 8px; border: 1px solid #f3f4f6; margin-bottom: 16px;">
                                            <tr>
                                                <td style="padding: 16px;">
                                                    <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Booking Reference</p>
                                                    <p style="margin: 0; font-size: 18px; font-weight: 700; color: #1f2937;">{{ $booking->uuid }}</p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Card 2: Booking Name -->
                                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 8px; border: 1px solid #f3f4f6; margin-bottom: 16px;">
                                            <tr>
                                                <td style="padding: 16px;">
                                                    <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Booking Name As Per I/C</p>
                                                    <p style="margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #1f2937;">{{ $booking->booking_name }}</p>
                                                    <p style="margin: 0 0 2px 0; font-size: 13px; color: #4b5563;">Phone: {{ $booking->phone_number }}</p>
                                                    <p style="margin: 0; font-size: 13px; color: #4b5563;">Email: {{ $booking->booking_email }}</p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Card 3: Rooms -->
                                        @if($booking->rooms->count() > 0)
                                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 8px; border: 1px solid #f3f4f6; margin-bottom: 16px;">
                                            <tr>
                                                <td style="padding: 16px;">
                                                    <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Rooms</p>
                                                    @foreach($booking->rooms as $index => $room)
                                                    <p style="margin: 0 0 4px 0; font-size: 13px; color: #4b5563; font-weight: 500;">Room {{ $index + 1 }}: {{ $room->roomType->name ?? 'N/A' }} ({{ $room->adults }} Adults, {{ $room->children }} Children, {{ $room->infants }} Infants)</p>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        </table>
                                        @endif
                                    </td>

                                    <td width="20"></td>

                                    <td valign="top" width="260">
                                        <!-- RIGHT COLUMN -->

                                        <!-- Card 4: Guests -->
                                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 8px; border: 1px solid #f3f4f6; margin-bottom: 16px;">
                                            <tr>
                                                <td style="padding: 16px;">
                                                    <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Guests</p>
                                                    <p style="margin: 0; font-size: 18px; font-weight: 700; color: #1f2937;">{{ $booking->adults }} Adults, {{ $booking->children }} Children, {{ $booking->infants }} Infants</p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Card 5: Duration -->
                                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 8px; border: 1px solid #f3f4f6; margin-bottom: 16px;">
                                            <tr>
                                                <td style="padding: 16px;">
                                                    <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Duration</p>
                                                    <p style="margin: 0; font-size: 18px; font-weight: 700; color: #1f2937;">{{ $days }} Days, {{ $nights }} Nights</p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Card 6: Check-in / Check-out (side by side) -->
                                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 8px; border: 1px solid #f3f4f6; margin-bottom: 16px;">
                                            <tr>
                                                <td style="padding: 16px;">
                                                    <table width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <!-- Check-in sub-box -->
                                                            <td valign="top" width="48%" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #bfdbfe; padding: 12px;">
                                                                <table cellpadding="0" cellspacing="0" style="margin: 0 0 8px 0;">
                                                                    <tr>
                                                                        <td style="width: 28px; height: 28px; background-color: #d1fae5; border-radius: 50%; text-align: center; vertical-align: middle; font-size: 14px; color: #059669; font-weight: 700;">&rarr;</td>
                                                                    </tr>
                                                                </table>
                                                                <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 600; color: #059669;">Check-in</p>
                                                                <p style="margin: 0 0 2px 0; font-size: 14px; font-weight: 700; color: #1f2937;">{{ $booking->start_date->format('d M Y') }}</p>
                                                                <p style="margin: 0; font-size: 11px; color: #6b7280;">{{ $booking->start_date->format('l') }}</p>
                                                            </td>
                                                            <td width="4%"></td>
                                                            <!-- Check-out sub-box -->
                                                            <td valign="top" width="48%" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #bfdbfe; padding: 12px;">
                                                                <table cellpadding="0" cellspacing="0" style="margin: 0 0 8px 0;">
                                                                    <tr>
                                                                        <td style="width: 28px; height: 28px; background-color: #fef3c7; border-radius: 50%; text-align: center; vertical-align: middle; font-size: 14px; color: #d97706; font-weight: 700;">&larr;</td>
                                                                    </tr>
                                                                </table>
                                                                <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 600; color: #d97706;">Check-out</p>
                                                                <p style="margin: 0 0 2px 0; font-size: 14px; font-weight: 700; color: #1f2937;">{{ $booking->end_date->format('d M Y') }}</p>
                                                                <p style="margin: 0; font-size: 11px; color: #6b7280;">{{ $booking->end_date->format('l') }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Total Amount -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #eef2ff; border-radius: 8px; border: 1px solid #e0e7ff; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size: 16px; font-weight: 600; color: #312e81;">Total Amount</td>
                                                <td style="text-align: right; font-size: 24px; font-weight: 700; color: #4f46e5;">MYR {{ number_format($booking->total_price, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Special Remarks -->
                            @if($booking->special_remarks)
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 10px;">
                                <tr>
                                    <td style="padding: 12px 16px; background-color: #fffbeb; border-radius: 8px; border-left: 4px solid #f59e0b;">
                                        <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px;">Special Remarks</p>
                                        <p style="margin: 0; font-size: 14px; color: #1f2937;">{{ $booking->special_remarks }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                This is an automated booking confirmation email. Please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
