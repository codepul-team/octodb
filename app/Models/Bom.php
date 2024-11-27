<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'query',
        'qty',
        'is_match',
        'part',
        'part_description',
        'description',
        'schematic_reference',
        'internal_part_no',
        'lifecycle',
        'lead_time',
        'rohs',
        'digi_key',
        'mouser',
        'newark',
        'online_component',
        'rs_component',
        'distributor',
        'unit_price',
        'line_total',
        'bacth_total',
        'note'
    ];
}
