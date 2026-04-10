<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'status'])]
class Driver extends Model
{
    use HasFactory;

    const STATUS_AVAILABLE = 'available';

    const STATUS_BUSY = 'busy';
}
