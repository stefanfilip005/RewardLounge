<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FutureShift extends Model
{
    use HasFactory;
    protected $table = 'futureShifts';
    protected $fillable = [
        'Teil', 'Verwendung', 'Schicht', 'RemoteId', 'KlassId', 'IstVollst',
        'Datum', 'Beginn', 'Ende', 'PoolBeginn', 'PoolEnde', 'Bezeichnung',
        'ObjektId', 'ObjektBezeichnung1', 'ObjektBezeichnung2', 'ObjektInfo',
        'PlanInfo', 'IstForderer', 'VaterId', 'IstOptional', 'PoolId', 'PoolTeil',
        'DienstartId', 'DienstartBeschreibung', 'ChgUserAnzeigename', 'ChgUserLoginname',
        'ChgDate', 'AbteilungId', 'AbteilungBezeichnung', 'AbteilungKZ', 'Info', 'TimeStamp',
        'Processed', 'MessageSent', 'created_at', 'updated_at'
    ];
}
