<?php

namespace App\Http\Controllers;

use App\Models\ECDCDomain;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ECDCDomainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'ECDC Domains',
            'title' => 'ECDC Domain Management',
            'crumb' => ['ECDC Domains' => '/ecdc_domains'],
        ];

        $domains = ECDCDomain::withCount('competencies')->orderBy('id')->get();

        return view('ecdc_domains.index', compact(
            'page',
            'domains'
        ));
    }

    /**
     * Display the specified resource together with its competencies.
     *
     * @return Response
     */
    public function show(ECDCDomain $ecdc_domain)
    {
        $page = [
            'name' => 'ECDC Domains',
            'title' => 'ECDC Competencies',
            'crumb' => [
                'ECDC Domains' => '/ecdc_domains',
                $ecdc_domain->domain => '/ecdc_domains/'.$ecdc_domain->id,
            ],
        ];

        $competencies = $ecdc_domain->competencies()
            ->withCount('studentResults')
            ->orderBy('id')
            ->get();

        return view('ecdc_domains.show', compact(
            'page',
            'ecdc_domain',
            'competencies'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        if (ECDCDomain::whereRaw('LOWER(domain) = ?', [mb_strtolower($request->domain)])->exists()) {
            return redirect()->to(url()->previous())->withErrors(['error' => 'ECDC domain already exists!']);
        }

        ECDCDomain::create(['domain' => $request->domain]);

        return redirect('/ecdc_domains')->with('success', 'New ECDC domain has been added successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tbl_ecdc_domains,id',
            'domain' => 'required|string|max:255',
        ]);

        $domain = ECDCDomain::findOrFail($request->id);

        $duplicate = ECDCDomain::whereRaw('LOWER(domain) = ?', [mb_strtolower($request->domain)])
            ->where('id', '<>', $domain->id)
            ->exists();

        if ($duplicate) {
            return redirect()->to(url()->previous())->withErrors(['error' => 'ECDC domain already exists!']);
        }

        $domain->domain = $request->domain;
        $domain->save();

        return back()->with('success', 'ECDC domain has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tbl_ecdc_domains,id',
        ]);

        $domain = ECDCDomain::withCount('competencies')->findOrFail($request->id);

        if ($domain->competencies_count > 0) {
            $noun = ($domain->competencies_count == 1) ? 'competency' : 'competencies';
            $message = $domain->competencies_count.' '.$noun.' found under '.$domain->domain.' domain.';

            return redirect()->to(url()->previous())->withErrors(['error' => $message]);
        }

        $domain->delete();

        return back()->with('success', 'ECDC domain has been deleted successfully.');
    }
}
