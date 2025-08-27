<?php

namespace App\Services;

use App\Models\Report;

class ReportAnalyticsService
{
    public function getReportAnalytics()
    {
        return [
            'total' => Report::count(),
            'pdf_count' => Report::where('export_format', 'PDF')->count(),
            'excel_count' => Report::where('export_format', 'Excel')->count(),
            'today' => Report::whereDate('generated_date', now())->count(),
            'this_week' => Report::whereBetween('generated_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Report::whereMonth('generated_date', now()->month)->count(),
        ];
    }
}
