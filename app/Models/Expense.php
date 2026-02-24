<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'property_id',
        'amount',
        'expense_date',
        'category',
        'notes',
    ];

    /**
     * Une dépense appartient à une propriété spécifique.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}