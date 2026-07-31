<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Offering;
use App\Models\Pledge;
use App\Models\Tithe;
use Illuminate\Support\Carbon;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $offeringsMonth = (float) Offering::whereBetween('collection_date', [$monthStart, $monthEnd])->sum('amount');
        $tithesMonth = (float) Tithe::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount');
        $expensesMonth = (float) Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount');
        $incomeMonth = $offeringsMonth + $tithesMonth;

        $stats = [
            'offerings_month' => $offeringsMonth,
            'tithes_month' => $tithesMonth,
            'expenses_month' => $expensesMonth,
            'income_month' => $incomeMonth,
            'net_month' => $incomeMonth - $expensesMonth,
            'pledged_total' => (float) Pledge::sum('amount'),
            'pledged_paid' => (float) Pledge::sum('amount_paid'),
            'pledged_remaining' => (float) (Pledge::selectRaw('SUM(amount - amount_paid) as remaining')->value('remaining') ?? 0),
            'offerings_all' => (float) Offering::sum('amount'),
            'tithes_all' => (float) Tithe::sum('amount'),
            'expenses_all' => (float) Expense::sum('amount'),
        ];

        $offeringsByCategory = Offering::selectRaw('category, SUM(amount) as total')
            ->whereBetween('collection_date', [$monthStart, $monthEnd])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $expensesByCategory = Expense::selectRaw('category, SUM(amount) as total')
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $recentTithes = Tithe::with('member')
            ->latest('payment_date')
            ->latest('id')
            ->take(6)
            ->get();

        $recentOfferings = Offering::latest('collection_date')
            ->latest('id')
            ->take(6)
            ->get();

        $recentExpenses = Expense::latest('expense_date')
            ->latest('id')
            ->take(6)
            ->get();

        $openPledges = Pledge::with('member')
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('due_date')
            ->take(6)
            ->get();

        $monthLabel = Carbon::now()->translatedFormat('F Y');

        return view('finance.dashboard', compact(
            'stats',
            'offeringsByCategory',
            'expensesByCategory',
            'recentTithes',
            'recentOfferings',
            'recentExpenses',
            'openPledges',
            'monthLabel'
        ));
    }
}
