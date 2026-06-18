<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Goal;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $totalTasks = Task::count();
        $totalGoals = Goal::count();
        $activeUsers = User::whereHas('tasks', function ($q) {
            $q->where('updated_at', '>=', now()->subDays(7));
        })->count();

        $recentUsers = User::latest()->take(10)->get();

        return view('admin.index', compact(
            'totalUsers', 'totalTasks', 'totalGoals', 'activeUsers', 'recentUsers'
        ));
    }

    public function users(Request $request)
    {
        $users = User::withCount(['tasks', 'goals'])->latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:user,admin']);
        $user->update(['role' => $request->role]);
        return back()->with('success', "User role updated to {$request->role}.");
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account from admin panel.');
        }
        $user->delete();
        return back()->with('success', 'User account deleted successfully.');
    }
}
