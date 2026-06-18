<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Task;
use App\Models\Goal;
use App\Models\Note;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $setting = $user->setting;

        if (!$setting) {
            // Provision settings in case it's missing (fallback safety)
            $setting = Setting::create([
                'user_id' => $user->id,
                'theme' => 'light',
                'daily_streak' => 0,
                'points' => 0,
            ]);
        }

        return view('settings.index', compact('user', 'setting'));
    }

    /**
     * Patch theme settings asynchronously via AJAX.
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark'
        ]);

        $user = $request->user();
        $setting = $user->setting;
        
        $setting->update([
            'theme' => $request->theme
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Theme updated successfully'
        ]);
    }

    /**
     * Update notification options.
     */
    public function updateNotifications(Request $request)
    {
        $user = $request->user();
        $setting = $user->setting;

        $setting->update([
            'email_notifications' => $request->has('email_notifications'),
            'browser_notifications' => $request->has('browser_notifications'),
        ]);

        ActivityLog::log($user->id, "Updated Notification Preferences");

        return redirect()->route('settings.index')->with('success', 'Notification preferences updated!');
    }

    /**
     * Update user Profile (Avatar upload, Name, Email, Password change).
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // 1. Update Name and Email
        $user->name = $request->name;
        $user->email = $request->email;

        // 2. Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // 3. Password change
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The provided current password does not match.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        ActivityLog::log($user->id, "Updated Profile information");

        return redirect()->route('settings.index')->with('success', 'Profile information updated successfully!');
    }

    /**
     * Export JSON database backup of all user records.
     */
    public function exportBackup(Request $request)
    {
        $user = $request->user();

        // Package all user-owned data
        $data = [
            'backup_meta' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'timestamp' => Carbon::now()->toIso8601String(),
                'version' => '1.0'
            ],
            'categories' => Category::where('user_id', $user->id)->get()->map(function($c) {
                return [
                    'name' => $c->name,
                    'color' => $c->color,
                    'icon' => $c->icon
                ];
            })->toArray(),
            'tasks' => $user->tasks->map(function($t) {
                return [
                    'title' => $t->title,
                    'description' => $t->description,
                    'category_name' => $t->category?->name,
                    'priority' => $t->priority,
                    'status' => $t->status,
                    'due_date' => $t->due_date ? $t->due_date->toDateString() : null,
                    'due_time' => $t->due_time,
                    'repeat_type' => $t->repeat_type,
                    'estimated_minutes' => $t->estimated_minutes,
                ];
            })->toArray(),
            'goals' => $user->goals->map(function($g) {
                return [
                    'title' => $g->title,
                    'description' => $g->description,
                    'target_value' => $g->target_value,
                    'current_value' => $g->current_value,
                    'deadline' => $g->deadline ? $g->deadline->toDateString() : null,
                    'status' => $g->status,
                ];
            })->toArray(),
            'notes' => $user->notes->map(function($n) {
                return [
                    'title' => $n->title,
                    'content' => $n->content,
                ];
            })->toArray(),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT);
        $filename = 'taskflow_backup_' . Carbon::now()->format('Ymd_His') . '.json';

        ActivityLog::log($user->id, "Exported JSON Backup file");

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    /**
     * Restore user records from uploaded JSON backup file.
     */
    public function importRestore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json,txt' // Allow txt/json extensions
        ]);

        $user = $request->user();
        $file = $request->file('backup_file');
        
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !is_array($data)) {
            return back()->with('error', 'Invalid backup file structure or corrupted data.');
        }

        // 1. Recreate/link categories mapping
        $categoryMap = [];
        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $catData) {
                // Find existing system category or custom category
                $category = Category::where(function($q) use ($user) {
                        $q->whereNull('user_id')->orWhere('user_id', $user->id);
                    })
                    ->where('name', $catData['name'])
                    ->first();

                if (!$category) {
                    $category = Category::create([
                        'user_id' => $user->id,
                        'name' => $catData['name'],
                        'color' => $catData['color'] ?? '#4F46E5',
                        'icon' => $catData['icon'] ?? 'fa-folder',
                    ]);
                }
                $categoryMap[$catData['name']] = $category->id;
            }
        }

        // 2. Recreate Tasks
        if (isset($data['tasks']) && is_array($data['tasks'])) {
            foreach ($data['tasks'] as $taskData) {
                $catId = null;
                if (isset($taskData['category_name']) && isset($categoryMap[$taskData['category_name']])) {
                    $catId = $categoryMap[$taskData['category_name']];
                }

                Task::create([
                    'user_id' => $user->id,
                    'category_id' => $catId,
                    'title' => $taskData['title'],
                    'description' => $taskData['description'] ?? null,
                    'priority' => $taskData['priority'] ?? 'Medium',
                    'status' => $taskData['status'] ?? 'Pending',
                    'due_date' => $taskData['due_date'] ?? null,
                    'due_time' => $taskData['due_time'] ?? null,
                    'repeat_type' => $taskData['repeat_type'] ?? 'None',
                    'estimated_minutes' => $taskData['estimated_minutes'] ?? null,
                ]);
            }
        }

        // 3. Recreate Goals
        if (isset($data['goals']) && is_array($data['goals'])) {
            foreach ($data['goals'] as $goalData) {
                Goal::create([
                    'user_id' => $user->id,
                    'title' => $goalData['title'],
                    'description' => $goalData['description'] ?? null,
                    'target_value' => $goalData['target_value'] ?? 100,
                    'current_value' => $goalData['current_value'] ?? 0,
                    'deadline' => $goalData['deadline'] ?? null,
                    'status' => $goalData['status'] ?? 'In Progress',
                ]);
            }
        }

        // 4. Recreate Notes
        if (isset($data['notes']) && is_array($data['notes'])) {
            foreach ($data['notes'] as $noteData) {
                Note::create([
                    'user_id' => $user->id,
                    'title' => $noteData['title'],
                    'content' => $noteData['content'] ?? null,
                ]);
            }
        }

        ActivityLog::log($user->id, "Restored records from Backup file");

        return redirect()->route('settings.index')->with('success', 'Data restored successfully from backup!');
    }
}
