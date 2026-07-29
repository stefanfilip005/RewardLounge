<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Bestellung abholbereit</title>
    <!--[if mso]>
    <style type="text/css">
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        table td { font-family: Arial, sans-serif; }
        .fallback-total { background-color: #e8f5e9 !important; }
        .reminder-bg { background-color: #fff8e1 !important; }
    </style>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body bgcolor="#f0f2f5" style="margin:0; padding:0; width:100%; background-color:#f0f2f5; font-family:Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">

    <!-- Hidden preview text -->
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
        Deine Bestellung #{{ $order->id }} ist abholbereit!
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>

    <!-- Wrapper Table -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f0f2f5;">
        <tr>
            <td align="center" style="padding: 30px 10px;">

                <!-- Main Container 600px -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width:600px; width:100%; background-color:#ffffff;">

                    <!-- HEADER: Green band -->
                    <tr>
                        <td style="background-color:#2e7d32; padding: 30px 35px; text-align:center;">
                            <p style="margin:0 0 5px 0; color:#ffffff; font-size:22px; font-weight:bold;">Deine Bestellung ist abholbereit!</p>
                            <p style="margin:0 0 15px 0; color:#c8e6c9; font-size:14px;">Rotes Kreuz Hollabrunn &mdash; Reward Lounge</p>
                            <!-- Gold accent line -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="height:3px; background-color:#FFA500; font-size:0; line-height:0;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- BODY CONTENT -->
                    <tr>
                        <td style="padding: 30px 35px 10px 35px;">

                            <!-- Greeting -->
                            <p style="margin:0 0 15px 0; color:#333333; font-size:18px; font-weight:600;">Hallo {{ $order->employee->firstname }},</p>

                            <!-- Ready badge -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background-color:#e8f5e9; border-radius:20px; padding:8px 16px;">
                                        <span style="color:#2e7d32; font-size:14px; font-weight:700;">&#9733; Bestellung #{{ $order->id }} fertig!</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 25px 0; color:#555555; font-size:14px; line-height:1.6;">tolle Neuigkeiten! Deine Bestellung ist nun bereit f&#252;r die Abholung. Hier sind alle wichtigen Informationen:</p>

                        </td>
                    </tr>

                    <!-- PICK-UP INFO CARD (green) -->
                    <tr>
                        <td style="padding: 0 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#e8f5e9; border:1px solid #c8e6c9;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="margin:0 0 12px 0; color:#2e7d32; font-size:15px; font-weight:700; padding-bottom:8px; border-bottom:1px solid #c8e6c9;">Abholung</p>
                                        <!-- Location -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="65" style="padding:6px 0; color:#2e7d32; font-size:13px; font-weight:bold;">Ort</td>
                                                <td style="padding:6px 0; color:#333333; font-size:14px;"><strong>Bezirksstelle Hollabrunn</strong></td>
                                            </tr>
                                        </table>
                                        <!-- Time -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="65" style="padding:6px 0; color:#2e7d32; font-size:13px; font-weight:bold;">Zeit</td>
                                                <td style="padding:6px 0; color:#333333; font-size:14px;"><strong>Montag bis Freitag</strong> &mdash; 06:00 bis 22:00 Uhr</td>
                                            </tr>
                                        </table>
                                        <!-- Contact -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="65" style="padding:6px 0; color:#2e7d32; font-size:13px; font-weight:bold;">Kontakt</td>
                                                <td style="padding:6px 0; color:#333333; font-size:14px;">Melde dich bitte bei unseren <strong>hauptberuflichen Kolleg:innen</strong> an der Bezirksstelle.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ORDER INFO CARD -->
                    <tr>
                        <td style="padding: 0 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f8f9fa; border:1px solid #e9ecef;">
                                <tr>
                                    <td style="padding:20px;">
                                        <!-- Bestellnummer -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Bestellnummer</td>
                                                <td style="padding:4px 0; color:#333333; font-size:15px; font-weight:bold; color:#2e7d32;">#{{ $order->id }}</td>
                                            </tr>
                                        </table>
                                        <!-- Personalnummer -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Personalnummer</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ $order->remoteId }}</td>
                                            </tr>
                                        </table>
                                        <!-- Bestelldatum -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Bestelldatum</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ \Carbon\Carbon::parse($order->created_at_datetime)->timezone('Europe/Vienna')->format('d.m.Y \u\m H:i') }} Uhr</td>
                                            </tr>
                                        </table>
                                        <!-- Abholbereit seit -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Abholbereit seit</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ $order->state_3_datetime ? \Carbon\Carbon::parse($order->state_3_datetime)->timezone('Europe/Vienna')->format('d.m.Y \u\m H:i') . ' Uhr' : 'Nicht verfügbar' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- HANDLED BY -->
                    @if($handledBy)
                    <tr>
                        <td style="padding: 0 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f8f9fa; border:1px solid #e9ecef;">
                                <tr>
                                    <td style="padding:15px 20px;">
                                        <p style="margin:0 0 5px 0; color:#6c757d; font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:0.5px;">&#10003; Abholbereit gemacht von</p>
                                        <p style="margin:0; color:#333333; font-size:14px; font-weight:600;">{{ $handledBy->firstname }} {{ $handledBy->lastname }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    <!-- PRODUCTS SECTION TITLE -->
                    <tr>
                        <td style="padding: 0 35px 15px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="border-bottom:2px solid #2e7d32; padding:0 0 8px 0;">
                                        <span style="color:#333333; font-size:16px; font-weight:700;">Deine Produkte</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- PRODUCTS LIST -->
                    @foreach($order->orderItems as $item)
                    <tr>
                        <td style="padding: 0 35px 12px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #e9ecef;">
                                <tr>
                                    <!-- Quantity Column -->
                                    <td width="90" style="padding:12px 10px 12px 12px; background-color:#fafafa; text-align:center; vertical-align:middle;">
                                        <span style="color:#2e7d32; font-size:20px; font-weight:bold;">{{ $item->quantity }}&times;</span>
                                    </td>
                                    <!-- Product Details Column -->
                                    <td style="padding:12px 15px 12px 12px; vertical-align:middle;">
                                        <p style="margin:0 0 3px 0; color:#333333; font-size:14px; font-weight:600;">{{ $item->name }}</p>
                                        @if($item->slogan && $item->slogan !== '')
                                        <p style="margin:0 0 5px 0; color:#6c757d; font-size:12px;">{{ $item->slogan }}</p>
                                        @endif
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding-right:20px; color:#555555; font-size:12px;">Einzelpreis: {{ number_format($item->points, 0, ',', '.') }} Punkte</td>
                                                <td style="color:#2e7d32; font-size:13px; font-weight:700;">{{ number_format($item->points * $item->quantity, 0, ',', '.') }} Punkte</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    <!-- TOTAL ROW -->
                    <tr>
                        <td style="padding: 5px 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="fallback-total" style="background-color:#e8f5e9; border:2px solid #2e7d32;">
                                <tr>
                                    <td style="padding:15px 20px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td style="color:#333333; font-size:15px; font-weight:700;">Gesamtkosten</td>
                                                <td align="right" style="color:#2e7d32; font-size:18px; font-weight:800;">{{ number_format($order->total_points, 0, ',', '.') }} Punkte</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- REMINDER (yellow) -->
                    <tr>
                        <td style="padding: 0 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="reminder-bg" style="background-color:#fff8e1; border-left:4px solid #FFC107;">
                                <tr>
                                    <td style="padding:15px 20px;">
                                        <p style="margin:0 0 3px 0; color:#795548; font-size:14px; font-weight:700;">Wichtig</p>
                                        <p style="margin:0; color:#795548; font-size:13px; line-height:1.5;">Bitte gib bei der Abholung deine Personalnummer {{ $order->remoteId }} an. Falls du Fragen hast, wende dich gerne an unser Team.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                <!-- /Main Container -->

                <!-- FOOTER -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width:600px; width:100%; background-color:#f8f9fa; border-top:1px solid #e9ecef;">
                    <tr>
                        <td style="padding:25px 35px; text-align:center;">
                            <p style="margin:0 0 8px 0; color:#6c757d; font-size:12px;">{{ \Carbon\Carbon::now('Europe/Vienna')->format('d.m.Y \u\m H:i') }} Uhr</p>
                            <p style="margin:0 0 10px 0;">
                                <a href="https://intern.rkhl.at" style="color:#2e7d32; font-size:14px; font-weight:600; text-decoration:none;">&#8594; Weiter zu intern.rkhl.at</a>
                            </p>
                            <p style="margin:0; color:#6c757d; font-size:11px; font-weight:500;">Reward Lounge &mdash; Rotes Kreuz Hollabrunn</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
