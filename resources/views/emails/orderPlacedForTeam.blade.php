<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid lightgray;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #8B0000; /* Dunkelrot */
            color: #ffffff;
            padding: 10px;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-weight: bold;
        }
        .footer {
            background-color: #cccccc; /* Grau */
            color: #333333;
            font-size: 12px;
            text-align: center;
            padding: 10px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .body {
            padding: 20px;
            text-align: left;
            color: #333333;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            Rotes Kreuz Hollabrunn - Intern
        </div>
        <div class="body">
            <p>Liebe Moderatoren und Admins!</p>
            <p>Soeben ist eine neue Bestellung mit den folgenden Details bei uns eingegangen: </p>
            <p>Bestellnummer: {{ $order->id }}</p>
            <p>Personalnummer: {{ $order->remoteId }}</p>
            <p>Zusammenfassung der Bestellung:</p>
            <ul>
                @foreach($order->orderItems as $item)
                    <li>{{ $item->quantity }} x {{ $item->name }} [{{ $item->points }} Punkte]</li>
                @endforeach
            </ul>
            <p>Gesamtkosten der Bestellung: {{ $order->total_points }} Punkte</p>
        </div>
        <div class="footer">
            Datum: {{ \Carbon\Carbon::now('Europe/Vienna')->format('d.m.Y H:i') }}<br>
            <a href="https://intern.rkhl.at" style="color: #333333; text-decoration: none;">intern.rkhl.at</a>
        </div>
    </div>
</body>
</html>