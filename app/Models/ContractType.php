<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use Illuminate\Database\Eloquent\Model;
use App\Models\Contract;

class ContractType extends Model
{
    protected $fillable = [
        'hr_id','name', 'description'];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
