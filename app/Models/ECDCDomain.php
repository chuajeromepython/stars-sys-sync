<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECDCDomain extends Model
{
    use HasFactory;
    // use \OwenIt\Auditing\Auditable;

    protected $table = 'tbl_ecdc_domains';

    protected $fillable = ['domain'];

    public function competencies()
    {
        return $this->hasMany(ECDCCompetency::class, 'domain_id');
    }

    /**
     * Whether the domain can be removed safely, i.e. it holds no competency.
     */
    public function isDeletable(): bool
    {
        return ! $this->competencies()->exists();
    }
}
