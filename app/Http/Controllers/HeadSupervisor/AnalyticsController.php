<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $totalApplicants = InternDetail::count();
        $activeInterns = InternDetail::whereNotNull('office_id')->count();
        $completedCount = InternDetail::whereHas('certificate', function ($q) {
            $q->where('status', 'published');
        })->count();

        $completionRate = $totalApplicants > 0 ? ($completedCount / $totalApplicants) * 100 : 0;
        $partnerCount = InternDetail::distinct('school')->count();
        $departments = Office::withCount('internDetails')->get();

        $schoolDist = InternDetail::select('school', DB::raw('count(*) as total'))
            ->groupBy('school')
            ->orderBy('total', 'desc')
            ->get();

        // Generate dynamic labels for the last 6 months
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->format('M');
        })->values()->toArray();

        // Real Trend Data: Monthly Applicants
        $applicantsTrends = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);

            return User::whereHas('role', function ($q) {
                $q->where('name', 'intern');
            })
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        })->values()->toArray();

        // Real Trend Data: Monthly Completions
        $completionTrends = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);

            return Certificate::where('status', 'published')
                ->whereMonth('published_at', $date->month)
                ->whereYear('published_at', $date->year)
                ->count();
        })->values()->toArray();

        // Real Trend Data: Monthly Completion Rate (%)
        $completionRateTrends = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);

            $applicants = User::whereHas('role', function ($q) {
                $q->where('name', 'intern');
            })
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $completions = Certificate::where('status', 'published')
                ->whereMonth('published_at', $date->month)
                ->whereYear('published_at', $date->year)
                ->count();

            return $applicants > 0 ? round(($completions / $applicants) * 100, 1) : 0;
        })->values()->toArray();

        $schoolLabels = $schoolDist->pluck('school')->toArray();
        $schoolTotals = $schoolDist->pluck('total')->toArray();

        // Detailed reports search, pagination and date discretion filtering logic
        $search = $request->input('search');
        $filterType = $request->input('filter_type', 'all'); // 'all', 'annual', 'monthly'
        $month = $request->input('month');
        $year = $request->input('year', now()->year);

        $reportsQuery = InternDetail::with(['office', 'user', 'certificate'])
            ->with(['journals' => function ($query) {
                $query->latest('created_at');
            }]);

        if ($search) {
            $reportsQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($filterType === 'annual') {
            $reportsQuery->whereYear('created_at', $year);
        } elseif ($filterType === 'monthly') {
            if ($month) {
                $reportsQuery->whereMonth('created_at', $month);
            }
            $reportsQuery->whereYear('created_at', $year);
        }

        $reports = $reportsQuery->orderBy('last_name', 'asc')->paginate(10);

        return view('head-supervisor.analytics', compact(
            'totalApplicants',
            'activeInterns',
            'completionRate',
            'partnerCount',
            'departments',
            'schoolDist',
            'months',
            'applicantsTrends',
            'completionTrends',
            'completionRateTrends',
            'schoolLabels',
            'schoolTotals',
            'reports'
        ));
    }

    public function exportPDF(Request $request)
    {
        // 1. Handle the Logo encoding
        // Adjust 'images/logo.png' to your actual logo path in the public folder
        $logoSrc = null;
        $path = public_path('images/blue-smartern.png');

        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoSrc = 'data:image/'.$type.';base64,'.base64_encode($data);
        }

        // 2. Prepare Data for the template
        $data = [
            'totalApplicants' => InternDetail::count(),
            'activeInterns' => InternDetail::whereNotNull('office_id')->count(),
            'departments' => Office::withCount('internDetails')->get(),
            'date' => now()->format('F d, Y'),
            'logoSrc' => $logoSrc,
            // These come from the hidden inputs in your Analytics view
            'completionChart' => $request->input('completion_chart_image'),
            'schoolChart' => $request->input('school_chart_image'),
        ];

        // 3. Generate PDF
        $pdf = Pdf::loadView('exports.analytics-pdf', $data);

        // Optional: Set paper size to A4
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('SmarTern-Analytics-Report-'.now()->format('Ymd').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->input('search');
        $filterType = $request->input('filter_type', 'all');
        $month = $request->input('month');
        $year = $request->input('year', now()->year);

        return Excel::download(new class($search, $filterType, $month, $year) implements FromCollection, WithHeadings, WithMapping
        {
            public function __construct(
                private ?string $search,
                private string $filterType,
                private ?string $month,
                private int $year
            ) {}

            public function collection()
            {
                $query = InternDetail::with(['office', 'certificate']);

                if ($this->search) {
                    $query->where(function ($q) {
                        $q->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%");
                    });
                }

                if ($this->filterType === 'annual') {
                    $query->whereYear('created_at', $this->year);
                } elseif ($this->filterType === 'monthly') {
                    if ($this->month) {
                        $query->whereMonth('created_at', $this->month);
                    }
                    $query->whereYear('created_at', $this->year);
                }

                return $query->get();
            }

            public function headings(): array
            {
                return ['Full Name', 'University/School', 'Course', 'Office Assigned', 'Hours Rendered', 'Status'];
            }

            public function map($intern): array
            {
                return [
                    $intern->first_name.' '.$intern->last_name,
                    $intern->school,
                    $intern->course,
                    $intern->office ? $intern->office->name : 'Unassigned',
                    (int) $intern->hours_rendered,
                    $intern->certificate && $intern->certificate->status === 'published' ? 'Completed' : 'Ongoing',
                ];
            }
        }, 'SmarTern-Intern-Masterlist-'.now()->format('Ymd').'.xlsx');
    }

    public function exportReportExcel(Request $request)
    {
        $search = $request->input('search');
        $filterType = $request->input('filter_type', 'all');
        $month = $request->input('month');
        $year = $request->input('year', now()->year);

        return Excel::download(new class($search, $filterType, $month, $year) implements FromCollection, WithHeadings, WithMapping
        {
            public function __construct(
                private ?string $search,
                private string $filterType,
                private ?string $month,
                private int $year
            ) {}

            public function collection()
            {
                $query = InternDetail::with(['office', 'certificate', 'journals' => function ($q) {
                    $q->latest('created_at');
                }]);

                if ($this->search) {
                    $query->where(function ($q) {
                        $q->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%");
                    });
                }

                if ($this->filterType === 'annual') {
                    $query->whereYear('created_at', $this->year);
                } elseif ($this->filterType === 'monthly') {
                    if ($this->month) {
                        $query->whereMonth('created_at', $this->month);
                    }
                    $query->whereYear('created_at', $this->year);
                }

                return $query->get();
            }

            public function headings(): array
            {
                return ['Intern Name', 'Office', 'Last Activity Date', 'Last Journal Entry', 'Total Hours', 'Certification Status'];
            }

            public function map($intern): array
            {
                $lastJournal = $intern->journals->first();

                return [
                    $intern->first_name.' '.$intern->last_name,
                    $intern->office ? $intern->office->name : 'N/A',
                    $lastJournal ? $lastJournal->created_at->format('Y-m-d H:i') : 'No activity',
                    $lastJournal ? $lastJournal->content : 'N/A',
                    (int) $intern->hours_rendered,
                    $intern->certificate ? ucfirst($intern->certificate->status) : 'Ongoing',
                ];
            }
        }, 'SmarTern-Detailed-Report-'.now()->format('Ymd').'.xlsx');
    }
}
