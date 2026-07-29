<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Neue Bestellung</title>
    <!--[if mso]>
    <style type="text/css">
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        table td { font-family: Arial, sans-serif; }
        .fallback-total { background-color: #fff8f8 !important; }
        .action-info-bg { background-color: #e3f2fd !important; }
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
        Neue Bestellung #{{ $order->id }} ist eingegangen.
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>

    <!-- Wrapper Table -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f0f2f5;">
        <tr>
            <td align="center" style="padding: 30px 10px;">

                <!-- Main Container 600px -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width:600px; width:100%; background-color:#ffffff;">

                    <!-- HEADER: Dark blue band -->
                    <tr>
                        <td style="background-color:#1a1a2e; padding: 20px 35px 15px 35px; text-align:center;">
                            <!-- New Order badge -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                <tr>
                                    <td style="background-color:#FFD700; border-radius:3px; padding:4px 14px; margin-bottom:12px;">
                                        <span style="color:#1a1a2e; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">&#9889; Neue Bestellung</span>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0; color:#ffffff; font-size:20px; font-weight:bold;">Rotes Kreuz Hollabrunn</p>
                            <p style="margin:5px 0 0 0; color:#a0aec0; font-size:12px;">Reward Lounge &mdash; Team-Info</p>
                            <!-- Gold accent line -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top:15px;">
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
                            <p style="margin:0 0 15px 0; color:#333333; font-size:18px; font-weight:600;">Liebe Moderatoren und Admins,</p>

                            <!-- Alert Box -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#fff3e0; border-left:4px solid #FF9800;">
                                <tr>
                                    <td style="padding:15px 20px;">
                                        <p style="margin:0 0 3px 0; color:#e65100; font-size:14px; font-weight:700;">&#9998; Neue Bestellung eingegangen</p>
                                        <p style="margin:0; color:#e65100; font-size:13px; line-height:1.5;">Bitte pr&#252;fe und bearbeite die Bestellung zeitnah im System.</p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- EMPLOYEE INFO CARD -->
                    <tr>
                        <td style="padding: 0 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f8f9fa; border:1px solid #e9ecef;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="margin:0 0 12px 0; color:#333333; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; padding-bottom:8px; border-bottom:1px solid #e9ecef;">Besteller:in</p>
                                        <!-- Name -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="130" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Name</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ $order->employee->firstname }} {{ $order->employee->lastname }}</td>
                                            </tr>
                                        </table>
                                        <!-- Personalnummer -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="130" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Personalnummer</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ $order->remoteId }}</td>
                                            </tr>
                                        </table>
                                        <!-- Email -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="130" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">E-Mail</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ $order->employee->email ?? 'Nicht angegeben' }}</td>
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
                                                <td style="padding:4px 0; color:#333333; font-size:15px; font-weight:bold; color:#1a1a2e;">#{{ $order->id }}</td>
                                            </tr>
                                        </table>
                                        <!-- Bestelldatum -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Bestelldatum</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ \Carbon\Carbon::parse($order->created_at_datetime)->timezone('Europe/Vienna')->format('d.m.Y \u\m H:i') }} Uhr</td>
                                            </tr>
                                        </table>
                                        <!-- Status -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Status</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ $order->state == 0 ? 'Offen' : 'Status ' . $order->state }}</td>
                                            </tr>
                                        </table>
                                        @if($order->note)
                                        <!-- Hinweise -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; font-weight:500;">Hinweise</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; font-weight:600;">{{ $order->note }}</td>
                                            </tr>
                                        </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- PRODUCTS SECTION TITLE -->
                    <tr>
                        <td style="padding: 0 35px 15px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="border-bottom:2px solid #1a1a2e; padding:0 0 8px 0;">
                                        <span style="color:#333333; font-size:16px; font-weight:700;">Bestellte Produkte</span>
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
                                        <span style="color:#1a1a2e; font-size:20px; font-weight:bold;">{{ $item->quantity }}&times;</span>
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
                                                <td style="color:#1a1a2e; font-size:13px; font-weight:700;">{{ number_format($item->points * $item->quantity, 0, ',', '.') }} Punkte</td>
                                            </tr>
                                        </table>
                                        @if($item->note && $item->note !== '')
                                        <p style="margin:5px 0 0 0; color:#FF9800; font-size:12px;">&#9998; {{ $item->note }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    <!-- TOTAL ROW -->
                    <tr>
                        <td style="padding: 5px 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="fallback-total" style="background-color:#fff8f8; border:2px solid #1a1a2e;">
                                <tr>
                                    <td style="padding:15px 20px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td style="color:#333333; font-size:15px; font-weight:700;">Gesamtkosten</td>
                                                <td align="right" style="color:#1a1a2e; font-size:18px; font-weight:800;">{{ number_format($order->total_points, 0, ',', '.') }} Punkte</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ACTION INFO -->
                    <tr>
                        <td style="padding: 0 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="action-info-bg" style="background-color:#e3f2fd; border-left:4px solid #2196f3;">
                                <tr>
                                    <td style="padding:15px 20px;">
                                        <p style="margin:0 0 3px 0; color:#1565c0; font-size:14px; font-weight:700;">Weiterer Ablauf</p>
                                        <p style="margin:0; color:#1565c0; font-size:13px; line-height:1.5;">Bitte &#252;berpr&#252;fe die Verf&#252;gbarkeit der Produkte und &#228;ndere den Status auf "Abholbereit", wenn die Bestellung fertig ist.</p>
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
                                <a href="https://intern.rkhl.at" style="color:#1a1a2e; font-size:14px; font-weight:600; text-decoration:none;">&#8594; Weiter zu intern.rkhl.at</a>
                            </p>
                            <p style="margin:0; color:#adb5bd; font-size:11px;">Dies ist eine interne Nachricht &mdash; nur f&#252;r Rotes Kreuz Hollabrunn Mitarbeiter:innen</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
