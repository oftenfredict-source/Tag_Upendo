<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Department;
use App\Models\Offering;
use App\Models\Expense;
use App\Models\Tithe;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $request->get('month', date('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth();

        // Basic Stats
        $monthlyOfferingsAmt = Offering::whereBetween('collection_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $monthlyTithesAmt = Tithe::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $monthlyExpensesAmt = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $monthlyIncomeAmt = $monthlyOfferingsAmt + $monthlyTithesAmt;

        $stats = [
            'total_members' => Member::count(),
            'total_departments' => Department::count(),
            'monthly_offerings' => $monthlyOfferingsAmt,
            'monthly_tithes' => $monthlyTithesAmt,
            'monthly_income' => $monthlyIncomeAmt,
            'monthly_expenses' => $monthlyExpensesAmt,
            'net_income' => $monthlyIncomeAmt - $monthlyExpensesAmt,
            'total_offerings' => Offering::sum('amount'),
            'total_tithes' => Tithe::sum('amount'),
            'total_income' => Offering::sum('amount') + Tithe::sum('amount'),
        ];

        // Members per Department
        $membersByDept = Department::withCount('members')->get()->map(function ($dept) {
            return [
                'label' => $dept->name,
                'value' => $dept->members_count
            ];
        });

        // Offerings by Category (Filtered by selected month)
        $offeringsByCat = Offering::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('collection_date', [$startOfMonth, $endOfMonth])
            ->groupBy('category')
            ->get()
            ->map(function ($offering) {
                return [
                    'label' => $offering->category,
                    'value' => (float) $offering->total
                ];
            });

        // Income distribution includes tithes as their own slice
        $incomeBySource = $offeringsByCat->values()->all();
        if ($monthlyTithesAmt > 0) {
            $incomeBySource[] = [
                'label' => __('Tithes'),
                'value' => (float) $monthlyTithesAmt,
            ];
        }

        // Expenses by Category (Filtered by selected month)
        $expensesByCat = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->groupBy('category')
            ->get()
            ->map(function ($expense) {
                return [
                    'label' => $expense->category,
                    'value' => (float) $expense->total
                ];
            });

        // Monthly Trends (Last 6 Months Trend - Combined)
        $offeringTrends = Offering::select(
            DB::raw('DATE_FORMAT(collection_date, "%Y-%m") as month'),
            DB::raw('SUM(amount) as total')
        )->groupBy('month')->orderBy('month', 'asc')->take(6)->get();

        $titheTrends = Tithe::select(
            DB::raw('DATE_FORMAT(payment_date, "%Y-%m") as month'),
            DB::raw('SUM(amount) as total')
        )->groupBy('month')->orderBy('month', 'asc')->take(6)->get();

        $expenseTrends = Expense::select(
            DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'),
            DB::raw('SUM(amount) as total')
        )->groupBy('month')->orderBy('month', 'asc')->take(6)->get();

        // Prepare labels for trend chart (last 6 months)
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $labels[] = $date->format('M Y');

            $offerings = $offeringTrends->firstWhere('month', $monthKey);
            $tithes = $titheTrends->firstWhere('month', $monthKey);
            $incomeData[] = ($offerings ? (float) $offerings->total : 0)
                + ($tithes ? (float) $tithes->total : 0);

            $expense = $expenseTrends->firstWhere('month', $monthKey);
            $expenseData[] = $expense ? (float) $expense->total : 0;
        }

        return view('reports.index', compact(
            'stats',
            'membersByDept',
            'offeringsByCat',
            'incomeBySource',
            'expensesByCat',
            'labels',
            'incomeData',
            'expenseData',
            'selectedMonth'
        ));
    }

    public function general()
    {
        // 1. Member Stats
        $memberStats = [
            'total' => Member::count(),
            'by_dept' => Department::withCount('members')->get(),
        ];

        // 2. Asset Stats
        $assetStats = [
            'total_items' => Asset::sum('quantity'),
            'by_status' => Asset::select('status', DB::raw('SUM(quantity) as count'))
                ->groupBy('status')->get(),
            'by_cat' => Asset::select('category', DB::raw('SUM(quantity) as count'))
                ->groupBy('category')->get(),
            'list' => Asset::all(),
        ];

        // 3. Financial Summary (All Time)
        $totalOfferings = Offering::sum('amount');
        $totalTithes = Tithe::sum('amount');
        $financials = [
            'total_income' => $totalOfferings + $totalTithes,
            'total_offerings' => $totalOfferings,
            'total_tithes' => $totalTithes,
            'total_expense' => Expense::sum('amount'),
            'by_income_cat' => Offering::select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')->get(),
            'by_expense_cat' => Expense::select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')->get(),
        ];

        return view('reports.general', compact('memberStats', 'assetStats', 'financials'));
    }
}
