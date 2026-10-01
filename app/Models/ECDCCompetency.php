<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECDCCompetency extends Model
{
    use HasFactory;
    // use \OwenIt\Auditing\Auditable;

    protected $table = 'tbl_ecdc_competencies';

    protected $fillable = ['domain_id', 'competency'];

    public function domain()
    {
        return $this->belongsTo(ECDCDomain::class, 'domain_id');
    }

    /**
     * The recorded scores that reference this competency.
     */
    public function studentResults()
    {
        return $this->hasMany(StudentECDC::class, 'ecdc_competency_id');
    }

    /**
     * Whether the competency can be removed safely, i.e. no recorded score
     * references it. Competency text may always be edited.
     */
    public function isDeletable(): bool
    {
        return ! $this->studentResults()->exists();
    }
}
