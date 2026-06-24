<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Services\Swm\DoEComplianceReportService;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;

class DoEComplianceReportController extends Controller
{
    public function __construct(protected DoEComplianceReportService $reportService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Dashboard and KPIs', [
            'only' => ['index', 'data', 'downloadPdf'],
        ]);
    }

    public function index(Request $request)
    {
        $year = $this->reportService->resolveYear($this->requestYear($request));
        $report = $this->reportService->build($year);
        $page_title = __('DoE Annual Compliance Report');

        return view('swm.dashboard.compliance-report.index', [
            'page_title' => $page_title,
            'report' => $report,
            'years' => $this->reportService->availableYears(),
            'selectedYear' => $year,
            'landfillTypeOptions' => $report['landfill_type_options'],
        ]);
    }

    public function data(Request $request)
    {
        $year = $this->reportService->resolveYear($this->requestYear($request));

        return response()->json($this->reportService->build($year));
    }

    public function downloadPdf(Request $request)
    {
        $payload = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'report_no' => ['nullable', 'string', 'max:255'],
            'report_date' => ['nullable', 'date'],
            'org_name' => ['nullable', 'string', 'max:500'],
            'population' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'chief_officer' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'daily_avg' => ['nullable', 'string', 'max:50'],
            'annual_total' => ['nullable', 'string', 'max:50'],
            'collected_formal' => ['nullable', 'string', 'max:50'],
            'stockpiled' => ['nullable', 'string', 'max:50'],
            'uncollected' => ['nullable', 'string', 'max:50'],
            'hazardous' => ['nullable', 'string', 'max:50'],
            'proc_organic' => ['nullable', 'string', 'max:50'],
            'proc_recycle' => ['nullable', 'string', 'max:50'],
            'proc_incineration' => ['nullable', 'string', 'max:50'],
            'proc_openburn' => ['nullable', 'string', 'max:50'],
            'proc_landfill' => ['nullable', 'string', 'max:50'],
            'num_landfill_sites' => ['nullable', 'string', 'max:50'],
            'collection_volume' => ['nullable', 'string', 'max:50'],
            'num_houses' => ['nullable', 'string', 'max:50'],
            'daily_pickup' => ['nullable', 'string', 'max:20'],
            'lifting_method' => ['nullable', 'string', 'max:20'],
            'proposals' => ['nullable', 'string'],
            'total_slums' => ['nullable', 'string', 'max:50'],
            'slums_sanitation' => ['nullable', 'string', 'max:50'],
            'mc_cases' => ['nullable', 'string', 'max:50'],
            'mc_convicted' => ['nullable', 'string', 'max:50'],
            'mc_fines' => ['nullable', 'string', 'max:50'],
            'mc_imprisoned' => ['nullable', 'string', 'max:50'],
            'gov_hospitals' => ['nullable', 'string', 'max:50'],
            'city_hospitals' => ['nullable', 'string', 'max:50'],
            'private_hospitals' => ['nullable', 'string', 'max:50'],
            'medical_compliance' => ['nullable', 'string', 'max:20'],
            'medical_issues' => ['nullable', 'string'],
            'step_dairy' => ['nullable', 'string'],
            'step_slaughter' => ['nullable', 'string'],
            'step_construction' => ['nullable', 'string'],
            'step_encroachment' => ['nullable', 'string'],
            'landfill_types' => ['nullable', 'array'],
            'landfill_types.*.name' => ['nullable', 'string', 'max:255'],
            'landfill_types.*.type' => ['nullable', 'string', 'max:255'],
            'landfill_types.*.capacity' => ['nullable', 'string', 'max:50'],
            'landfill_types.*.unit' => ['nullable', 'string', 'max:50'],
            'landfill_sites' => ['nullable', 'array'],
            'landfill_sites.*.landfill_id' => ['nullable', 'string', 'max:50'],
            'landfill_sites.*.name' => ['nullable', 'string', 'max:255'],
            'landfill_sites.*.area' => ['nullable', 'string', 'max:50'],
            'landfill_sites.*.unit' => ['nullable', 'string', 'max:50'],
            'landfill_sites.*.wb' => ['nullable', 'string', 'max:10'],
            'landfill_sites.*.bw' => ['nullable', 'string', 'max:10'],
            'landfill_sites.*.lt' => ['nullable', 'string', 'max:10'],
            'landfill_sites.*.pax' => ['nullable', 'string', 'max:50'],
            'landfill_sites.*.cv' => ['nullable', 'string', 'max:10'],
            'landfill_sites.*.gas' => ['nullable', 'string', 'max:10'],
            'landfill_sites.*.lc' => ['nullable', 'string', 'max:10'],
            'equipment' => ['nullable', 'array'],
            'equipment.*.name' => ['nullable', 'string', 'max:255'],
            'equipment.*.eq' => ['nullable', 'string', 'max:255'],
            'equipment.*.count' => ['nullable', 'string', 'max:50'],
            'door_orgs' => ['nullable', 'array'],
            'door_orgs.*.name' => ['nullable', 'string', 'max:255'],
            'door_orgs.*.desc' => ['nullable', 'string'],
            'bins' => ['nullable', 'array'],
            'bins.*.type' => ['nullable', 'string', 'max:500'],
            'bins.*.size' => ['nullable', 'string', 'max:255'],
            'bins.*.unit' => ['nullable', 'string', 'max:50'],
            'bins.*.count' => ['nullable', 'string', 'max:50'],
            'transport' => ['nullable', 'array'],
            'transport.*.type' => ['nullable', 'string', 'max:255'],
            'transport.*.existing' => ['nullable', 'string', 'max:50'],
            'transport.*.required' => ['nullable', 'string', 'max:50'],
            'contracts' => ['nullable', 'array'],
            'contracts.*.person' => ['nullable', 'string', 'max:255'],
            'contracts.*.tech' => ['nullable', 'string', 'max:255'],
            'contracts.*.dur' => ['nullable', 'string', 'max:255'],
            'contracts.*.addr' => ['nullable', 'string'],
        ]);

        $pdf = PDF::loadView('swm.dashboard.compliance-report.pdf', [
            'form' => $payload,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', '15mm')
            ->setOption('margin-bottom', '15mm')
            ->setOption('margin-left', '25.4mm')
            ->setOption('margin-right', '25.4mm')
            ->setOption('enable-smart-shrinking', false)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('encoding', 'UTF-8')
            ->setOption('enable-local-file-access', true)
            ->setOption('footer-line', false)
            ->setOption('footer-html', null)
            ->setOption('enable-javascript', false)
            ->setOption('javascript-delay', 0);

        $filename = 'doe-compliance-report-'.$payload['year'].'.pdf';

        return $pdf->download($filename);
    }

    protected function requestYear(Request $request): ?int
    {
        if (! $request->filled('year')) {
            return null;
        }

        return (int) $request->input('year');
    }
}
