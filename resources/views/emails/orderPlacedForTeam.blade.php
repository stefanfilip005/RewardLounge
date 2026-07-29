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
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
        Neue Bestellung #{{ $order->id }} von {{ $order->employee->firstname }} {{ $order->employee->lastname }}.
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f0f2f5;">
        <tr>
            <td align="center" style="padding:30px 10px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="width:100%; max-width:600px; background-color:#ffffff;">
                    <tr>
                        <td align="center" bgcolor="#1a1a2e" style="padding:27px 35px 0 35px; background-color:#1a1a2e;">
                            <p style="margin:0; color:#ffffff; font-size:21px; line-height:28px; font-weight:bold;">Rotes Kreuz Hollabrunn</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" bgcolor="#1a1a2e" style="padding:16px 35px 25px 35px; background-color:#1a1a2e;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                <tr>
                                    <td align="center" valign="middle" bgcolor="#FFD700" style="padding:9px 16px; background-color:#FFD700; color:#1a1a2e; font-size:11px; line-height:16px; mso-line-height-rule:exactly; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">
                                        &#9889;&nbsp; Neue Bestellung
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 35px 10px 35px;">
                            <p style="margin:0 0 15px 0; color:#333333; font-size:18px; line-height:24px; font-weight:bold;">Liebe Moderatoren und Admins,</p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" bgcolor="#fff3e0" style="background-color:#fff3e0; border-left:4px solid #FF9800;">
                                <tr>
                                    <td style="padding:14px 18px; color:#b34700; font-size:14px; line-height:20px; font-weight:bold;">&#9998;&nbsp; Neue Bestellung eingegangen</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" bgcolor="#f8f9fa" style="background-color:#f8f9fa; border:1px solid #e1e5e8;">
                                <tr>
                                    @if($employeePicturePath)
                                    <td width="100" align="center" valign="top" style="width:100px; padding:20px 0 20px 20px;">
                                        <img src="{{ $message->embed($employeePicturePath) }}" width="80" alt="{{ $order->employee->firstname }} {{ $order->employee->lastname }}" style="display:block; width:80px; max-width:80px; height:auto; border:0; outline:none; text-decoration:none;">
                                    </td>
                                    @endif
                                    <td valign="top" style="padding:20px;">
                                        <p style="margin:0 0 12px 0; padding-bottom:8px; border-bottom:1px solid #e1e5e8; color:#333333; font-size:13px; line-height:18px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">Besteller:in</p>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="120" style="padding:4px 0; color:#6c757d; font-size:13px; line-height:18px;">Name</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; line-height:18px; font-weight:bold;">{{ $order->employee->firstname }} {{ $order->employee->lastname }}</td>
                                            </tr>
                                            <tr>
                                                <td width="120" style="padding:4px 0; color:#6c757d; font-size:13px; line-height:18px;">Personalnummer</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; line-height:18px; font-weight:bold;">{{ $order->remoteId }}</td>
                                            </tr>
                                            <tr>
                                                <td width="120" style="padding:4px 0; color:#6c757d; font-size:13px; line-height:18px;">E-Mail</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; line-height:18px; font-weight:bold; word-break:break-word;">{{ $order->employee->email ?? 'Nicht angegeben' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 35px 25px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" bgcolor="#f8f9fa" style="background-color:#f8f9fa; border:1px solid #e1e5e8;">
                                <tr>
                                    <td style="padding:20px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; line-height:18px;">Bestellnummer</td>
                                                <td style="padding:4px 0; color:#1a1a2e; font-size:15px; line-height:18px; font-weight:bold;">#{{ $order->id }}</td>
                                            </tr>
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; line-height:18px;">Bestelldatum</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; line-height:18px; font-weight:bold;">{{ \Carbon\Carbon::parse($order->created_at_datetime)->timezone('Europe/Vienna')->format('d.m.Y \u\m H:i') }} Uhr</td>
                                            </tr>
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; line-height:18px;">Status</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; line-height:18px; font-weight:bold;">{{ $order->state == 0 ? 'Offen' : 'Status '.$order->state }}</td>
                                            </tr>
                                            @if($order->note)
                                            <tr>
                                                <td width="140" style="padding:4px 0; color:#6c757d; font-size:13px; line-height:18px; vertical-align:top;">Hinweis</td>
                                                <td style="padding:4px 0; color:#333333; font-size:14px; line-height:20px; font-weight:bold;">{{ $order->note }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 35px 15px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding:0 0 8px 0; border-bottom:2px solid #1a1a2e; color:#333333; font-size:16px; line-height:22px; font-weight:bold;">Bestellte Produkte</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @foreach($order->orderItems as $item)
                    <tr>
                        <td style="padding:0 35px 12px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #e1e5e8;">
                                <tr>
                                    <td width="112" align="center" valign="middle" bgcolor="#f8f9fa" style="width:112px; padding:12px; background-color:#f8f9fa;">
                                        @if(isset($productImagePaths[$item->getKey()]))
                                            <img src="{{ $message->embed($productImagePaths[$item->getKey()]) }}" width="88" alt="{{ $item->name }}" style="display:block; width:88px; max-width:88px; height:auto; max-height:88px; border:0; outline:none; text-decoration:none;">
                                        @else
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="88" height="70" style="width:88px; height:70px; border:1px solid #d8dde1;">
                                                <tr>
                                                    <td align="center" valign="middle" style="color:#8a939b; font-size:11px; line-height:15px;">Kein Bild</td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                    <td valign="top" style="padding:14px 15px;">
                                        <p style="margin:0 0 3px 0; color:#333333; font-size:14px; line-height:19px; font-weight:bold;">{{ $item->name }}</p>
                                        @if($item->slogan)
                                            <p style="margin:0 0 8px 0; color:#6c757d; font-size:12px; line-height:17px;">{{ \Illuminate\Support\Str::limit(strip_tags($item->slogan), 180) }}</p>
                                        @endif
                                        <p style="margin:0 0 3px 0; color:#555555; font-size:12px; line-height:17px;"><strong>Menge:</strong> {{ $item->quantity }}</p>
                                        <p style="margin:0; color:#555555; font-size:12px; line-height:17px;">Einzelpreis: {{ number_format($item->points, 0, ',', '.') }} Punkte</p>
                                        <p style="margin:2px 0 0 0; color:#8B0000; font-size:13px; line-height:18px; font-weight:bold;">Summe: {{ number_format($item->points * $item->quantity, 0, ',', '.') }} Punkte</p>
                                        @if($item->note)
                                            <p style="margin:8px 0 0 0; padding-top:7px; border-top:1px solid #eceff1; color:#b34700; font-size:12px; line-height:17px;">&#9998;&nbsp; {{ $item->note }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    <tr>
                        <td style="padding:5px 35px 30px 35px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" bgcolor="#8B0000" style="background-color:#8B0000; border:2px solid #8B0000;">
                                <tr>
                                    <td style="padding:17px 20px; color:#ffffff; font-size:15px; line-height:20px; font-weight:bold;">Gesamtpunkte</td>
                                    <td align="right" style="padding:17px 20px; color:#ffffff; font-size:20px; line-height:22px; font-weight:bold; white-space:nowrap;">{{ number_format($order->total_points, 0, ',', '.') }} Punkte</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" bgcolor="#f8f9fa" style="width:100%; max-width:600px; background-color:#f8f9fa; border-top:1px solid #e1e5e8;">
                    <tr>
                        <td align="center" style="padding:23px 35px;">
                            <p style="margin:0 0 9px 0; color:#6c757d; font-size:12px; line-height:17px;">{{ \Carbon\Carbon::now('Europe/Vienna')->format('d.m.Y \u\m H:i') }} Uhr</p>
                            <p style="margin:0;">
                                <a href="https://intern.rkhl.at" style="color:#1a1a2e; font-size:14px; line-height:20px; font-weight:bold; text-decoration:none;">&#8594; Weiter zu intern.rkhl.at</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
