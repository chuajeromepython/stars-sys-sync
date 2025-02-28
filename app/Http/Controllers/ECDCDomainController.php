<?php

namespace App\Http\Controllers;

use App\Models\ECDCDomain;
use App\Models\ECDCCompetency;

use Illuminate\Http\Request;

class ECDCDomainController extends Controller
{
   
    public function index()
    {
        $page = [
            'name'      =>  'ECDC Domain',
            'title'     =>  'ECDC Domain Reference Library',
            'crumb'     =>  array('ECDC Domain' => '/ecdc_domains')
        ];

        $domains = ECDCDomain::all();
        $competencies = array();

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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ECDCDomain  $eCDCDomain
     * @return \Illuminate\Http\Response
     */
    public function show(ECDCDomain $eCDCDomain)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ECDCDomain  $eCDCDomain
     * @return \Illuminate\Http\Response
     */
    public function edit(ECDCDomain $eCDCDomain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ECDCDomain  $eCDCDomain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ECDCDomain $eCDCDomain)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ECDCDomain  $eCDCDomain
     * @return \Illuminate\Http\Response
     */
    public function destroy(ECDCDomain $eCDCDomain)
    {
        //
    }
}
