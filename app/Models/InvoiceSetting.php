<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    protected $fillable = ['heading', 'footer'];

    public static function current(): self
    {
        return static::query()->first() ?: static::query()->create([
            'heading' => 'Invoice',
            'footer' => 'Thank you for staying with us.',
        ]);
    }
}
