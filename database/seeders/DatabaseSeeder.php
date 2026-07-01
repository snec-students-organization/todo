<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Task;
use App\Models\Goal;
use App\Models\Note;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Global Categories
        $categories = [
            ['name' => 'Study', 'color' => '#4F46E5', 'icon' => 'fa-book'],
            ['name' => 'Work', 'color' => '#10B981', 'icon' => 'fa-briefcase'],
            ['name' => 'Personal', 'color' => '#F59E0B', 'icon' => 'fa-user'],
            ['name' => 'Fitness', 'color' => '#EF4444', 'icon' => 'fa-dumbbell'],
            ['name' => 'Business', 'color' => '#3B82F6', 'icon' => 'fa-chart-line'],
            ['name' => 'Goals', 'color' => '#8B5CF6', 'icon' => 'fa-bullseye'],
            ['name' => 'Other', 'color' => '#6B7280', 'icon' => 'fa-folder'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 2. Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@taskflow.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 3. Create Regular User
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@taskflow.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Setup streak and points for regular user to look good immediately
        $userSetting = $user->setting;
        if ($userSetting) {
            $userSetting->update([
                'daily_streak' => 3,
                'last_completed_date' => Carbon::yesterday(),
                'points' => 120,
                'badges' => json_encode(['first_task', 'streak_3', 'first_goal']),
            ]);
        }

        // 4. Create Sample Goals for User
        $goal1 = Goal::create([
            'user_id' => $user->id,
            'title' => 'Learn Laravel 12 & Bootstrap 5',
            'description' => 'Build a complete productivity dashboard with charts and planner.',
            'target_value' => 10,
            'current_value' => 7,
            'deadline' => Carbon::today()->addMonths(2),
            'status' => 'In Progress',
        ]);

        $goal2 = Goal::create([
            'user_id' => $user->id,
            'title' => 'Run 50 Kilometers',
            'description' => 'Complete a total of 50km running this month.',
            'target_value' => 50,
            'current_value' => 15,
            'deadline' => Carbon::today()->endOfMonth(),
            'status' => 'In Progress',
        ]);

        $goal3 = Goal::create([
            'user_id' => $user->id,
            'title' => 'Read 3 Books',
            'description' => 'Read Atomic Habits, Deep Work, and Show Your Work.',
            'target_value' => 3,
            'current_value' => 3,
            'deadline' => Carbon::today()->subDays(5),
            'status' => 'Completed',
        ]);

        // 5. Create Sample Notes for User
        Note::create([
            'user_id' => $user->id,
            'title' => 'Weekly Reflection Idea',
            'content' => "What worked well this week?\n- Managed time blocks successfully\n- Pomodoro sessions are highly effective\n\nWhat can be improved?\n- Start exercise earlier in the morning.",
        ]);

        Note::create([
            'user_id' => $user->id,
            'title' => 'Shopping List',
            'content' => "- Coffee beans\n- Almond milk\n- Whole wheat bread\n- Chicken breast\n- Broccoli",
        ]);

        // 6. Create Sample Tasks for User
        $studyCat = Category::where('name', 'Study')->first();
        $workCat = Category::where('name', 'Work')->first();
        $personalCat = Category::where('name', 'Personal')->first();
        $fitnessCat = Category::where('name', 'Fitness')->first();

        // Overdue Task
        Task::create([
            'user_id' => $user->id,
            'category_id' => $workCat->id,
            'title' => 'Submit monthly invoice reports',
            'description' => 'Compile all bills and send to accountant.',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::today()->subDays(3),
            'due_time' => '17:00:00',
            'repeat_type' => 'Weekly',
            'estimated_minutes' => 45,
        ]);

        // Completed Tasks (for analytics and streaks)
        Task::create([
            'user_id' => $user->id,
            'category_id' => $fitnessCat->id,
            'title' => 'Morning jogging session',
            'description' => 'Ran 5km in the park.',
            'priority' => 'Medium',
            'status' => 'Completed',
            'due_date' => Carbon::yesterday(),
            'due_time' => '07:00:00',
            'repeat_type' => 'Daily',
            'estimated_minutes' => 30,
        ]);

        Task::create([
            'user_id' => $user->id,
            'category_id' => $personalCat->id,
            'title' => 'Buy grocery ingredients',
            'description' => 'Milk, eggs, oats, and bananas.',
            'priority' => 'Low',
            'status' => 'Completed',
            'due_date' => Carbon::yesterday(),
            'due_time' => '18:30:00',
            'repeat_type' => 'Weekly',
            'estimated_minutes' => 20,
        ]);

        Task::create([
            'user_id' => $user->id,
            'category_id' => $studyCat->id,
            'title' => 'Watch Laravel auth tutorial',
            'description' => 'Review token-based authentication options.',
            'priority' => 'Medium',
            'status' => 'Completed',
            'due_date' => Carbon::today()->subDays(2),
            'due_time' => '14:00:00',
            'repeat_type' => 'Weekly',
            'estimated_minutes' => 60,
        ]);

        // Today's Pending/In Progress Tasks
        Task::create([
            'user_id' => $user->id,
            'category_id' => $fitnessCat->id,
            'title' => 'Gym Workout Routine',
            'description' => 'Focus on leg day and core exercises.',
            'priority' => 'Medium',
            'status' => 'Pending',
            'due_date' => Carbon::today(),
            'due_time' => '06:00:00',
            'repeat_type' => 'Daily',
            'estimated_minutes' => 60,
        ]);

        Task::create([
            'user_id' => $user->id,
            'category_id' => $personalCat->id,
            'title' => 'Healthy breakfast block',
            'description' => 'Oatmeal, protein shake, and fruit.',
            'priority' => 'Low',
            'status' => 'Completed',
            'due_date' => Carbon::today(),
            'due_time' => '08:00:00',
            'repeat_type' => 'Daily',
            'estimated_minutes' => 30,
        ]);

        Task::create([
            'user_id' => $user->id,
            'category_id' => $studyCat->id,
            'title' => 'Study database optimization',
            'description' => 'Read about indexes, foreign keys, and query profiling.',
            'priority' => 'High',
            'status' => 'In Progress',
            'due_date' => Carbon::today(),
            'due_time' => '10:00:00',
            'repeat_type' => 'Daily',
            'estimated_minutes' => 90,
        ]);

        Task::create([
            'user_id' => $user->id,
            'category_id' => $workCat->id,
            'title' => 'Coding TaskFlow Dashboard',
            'description' => 'Integrate Chart.js analytics and build layout.',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::today(),
            'due_time' => '14:00:00',
            'repeat_type' => 'Daily',
            'estimated_minutes' => 180,
        ]);

        Task::create([
            'user_id' => $user->id,
            'category_id' => $studyCat->id,
            'title' => 'Reading Atomic Habits book',
            'description' => 'Read chapters 5 and 6 on habit building.',
            'priority' => 'Low',
            'status' => 'Pending',
            'due_date' => Carbon::today(),
            'due_time' => '19:00:00',
            'repeat_type' => 'Daily',
            'estimated_minutes' => 30,
        ]);

        // Future Tasks
        Task::create([
            'user_id' => $user->id,
            'category_id' => $workCat->id,
            'title' => 'Sprint planning meeting',
            'description' => 'Prepare agenda and slides for product roadmap review.',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::today()->addDay(),
            'due_time' => '10:30:00',
            'repeat_type' => 'Weekly',
            'estimated_minutes' => 60,
        ]);

        Task::create([
            'user_id' => $user->id,
            'category_id' => $personalCat->id,
            'title' => 'Weekend house cleaning',
            'description' => 'Vacuum, laundry, and kitchen cleanup.',
            'priority' => 'Medium',
            'status' => 'Pending',
            'due_date' => Carbon::today()->addDays(2),
            'due_time' => '09:00:00',
            'repeat_type' => 'Weekly',
            'estimated_minutes' => 120,
        ]);

        // 7. Seed activity log entries
        ActivityLog::log($user->id, 'Registered Account', 'Welcome to TaskFlow productivity system!');
        ActivityLog::log($user->id, 'Created Goal: Learn Laravel 12 & Bootstrap 5');
        ActivityLog::log($user->id, 'Created Goal: Run 50 Kilometers');
        ActivityLog::log($user->id, 'Created Note: Shopping List');
        ActivityLog::log($user->id, 'Completed Task: Buy grocery ingredients', 'Earned 10 points.');
        ActivityLog::log($user->id, 'Completed Task: Morning jogging session', 'Consecutive streak active.');
        ActivityLog::log($user->id, 'Unlocked Badge: First Steps');
        ActivityLog::log($user->id, 'Unlocked Badge: Streak Beginner');
    }
}
