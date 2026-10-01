<?php

namespace App\Http\Controllers;

use App\Models\ECDCCompetency;
use App\Models\ECDCDomain;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ECDCCompetencyController extends Controller
{
    /**
     * Store a newly created competency under the given domain.
     *
     * @return Response
     */
    public function store(Request $request, ECDCDomain $ecdc_domain)
    {
        $request->validate([
            'competency' => 'required|string|max:255',
        ]);

        if ($this->duplicateCompetencyExists($ecdc_domain, $request->competency)) {
            return redirect()->to(url()->previous())->withErrors(['error' => 'ECDC competency already exists under this domain!']);
        }

        $ecdc_domain->competencies()->create(['competency' => $request->competency]);

        return back()->with('success', 'New ECDC competency has been added successfully.');
    }

    /**
     * Update the competency text of a competency owned by the given domain.
     *
     * @return Response
     */
    public function update(Request $request, ECDCDomain $ecdc_domain)
    {
        $request->validate([
            'id' => 'required|integer|exists:tbl_ecdc_competencies,id',
            'competency' => 'required|string|max:255',
        ]);

        $competency = $this->findCompetency($ecdc_domain, $request->id);

        if ($this->duplicateCompetencyExists($ecdc_domain, $request->competency, $competency->id)) {
            return redirect()->to(url()->previous())->withErrors(['error' => 'ECDC competency already exists under this domain!']);
        }

        $competency->competency = $request->competency;
        $competency->save();

        return back()->with('success', 'ECDC competency has been updated successfully.');
    }

    /**
     * Remove the competency, unless recorded scores still reference it.
     *
     * @return Response
     */
    public function destroy(Request $request, ECDCDomain $ecdc_domain)
    {
        $request->validate([
            'id' => 'required|integer|exists:tbl_ecdc_competencies,id',
        ]);

        $competency = $this->findCompetency($ecdc_domain, $request->id);

        if (! $competency->isDeletable()) {
            $results = $competency->studentResults()->count();
            $noun = ($results == 1) ? 'result' : 'results';
            $message = $results.' recorded '.$noun.' reference '.$competency->competency.'.';

            return redirect()->to(url()->previous())->withErrors(['error' => $message]);
        }

        $competency->delete();

        return back()->with('success', 'ECDC competency has been deleted successfully.');
    }

    /**
     * Resolve a competency that belongs to the given domain, so an id from
     * another domain can never be edited or deleted through this endpoint.
     */
    private function findCompetency(ECDCDomain $ecdc_domain, int $id): ECDCCompetency
    {
        return $ecdc_domain->competencies()->where('id', $id)->firstOrFail();
    }

    /**
     * Competency names are unique per domain, compared without regard to case.
     */
    private function duplicateCompetencyExists(ECDCDomain $ecdc_domain, string $competency, ?int $exceptId = null): bool
    {
        $query = $ecdc_domain->competencies()
            ->whereRaw('LOWER(competency) = ?', [mb_strtolower($competency)]);

        if ($exceptId !== null) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->exists();
    }
}
