<?php

namespace App\Http\Controllers;

use App\Models\ECDCCompetency;
use App\Models\ECDCDomain;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ECDCDomainController extends Controller
{
    public function index()
    {
        $page = [
            'name' => 'ECDC Domain',
            'title' => 'ECDC Domain Reference Library',
            'crumb' => ['ECDC Domain' => '/ecdc_domains'],
        ];

        $domains = ECDCDomain::all();
        $competencies = [];

        foreach ($domains as $key => $domain) {
            $competencies[$domain->id] = ECDCCompetency::where('domain_id', $domain->id)->get();
        }
        $colors = [
            'red',
            'orange',
            'yellow',
            'green',
            'primary',
            'info',
            'purple',
        ];

        return view('ecdc_domains.index', compact(
            'page',
            'domains', 'competencies', 'colors'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(ECDCDomain $eCDCDomain)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(ECDCDomain $eCDCDomain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, ECDCDomain $eCDCDomain)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(ECDCDomain $eCDCDomain)
    {
        //
    }
}
