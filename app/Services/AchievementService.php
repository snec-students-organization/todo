<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Goal;
use App\Models\Note;
use App\Models\ActivityLog;
use Carbon\Carbon;

class AchievementService
{
    /**
     * Define the badges and their criteria.
     */
    public static function getAvailableBadges(): array
    {
        return [
            'first_task' => [
                'name' => 'First Steps',
                'description' => 'Completed your first task!',
                'icon' => 'fa-shoe-prints',
                'color' => '#10B981', // Emerald
            ],
            'tasks_10' => [
                'name' => 'Productivity Padawan',
                'description' => 'Completed 10 tasks.',
                'icon' => 'fa-graduation-cap',
                'color' => '#3B82F6', // Blue
            ],
            'tasks_50' => [
                'name' => 'Task Master',
                'description' => 'Completed 50 tasks.',
                'icon' => 'fa-crown',
                'color' => '#F59E0B', // Amber
            ],
            'streak_3' => [
                'name' => 'Streak Beginner',
                'description' => 'Maintained a 3-day task completion streak.',
                'icon' => 'fa-fire',
                'color' => '#EF4444', // Red
            ],
            'streak_7' => [
                'name' => 'Streak Champion',
                'description' => 'Maintained a 7-day task completion streak.',
                'icon' => 'fa-fire-flame-curved',
                'color' => '#8B5CF6', // Purple
            ],
            'first_goal' => [
                'name' => 'Goal Getter',
                'description' => 'Created your first goal!',
                'icon' => 'fa-bullseye',
                'color' => '#EC4899', // Pink
            ],
            'goal_completed' => [
                'name' => 'Goal Achiever',
                'description' => 'Completed your first goal!',
                'icon' => 'fa-trophy',
                'color' => '#F59E0B',
            ],
            'notes_5' => [
                'name' => 'Note Taker',
                'description' => 'Created 5 personal notes.',
                'icon' => 'fa-pen-to-square',
                'color' => '#6B7280', // Gray
            ],
        ];
    }

    /**
     * Check and update the streak and achievements of a user.
     * This is triggered whenever a task is completed.
     */
    public static function checkTaskCompletion(User $user)
    {
        $settings = $user->setting;
        if (!$settings) {
            return;
        }

        // 1. Calculate and update streak
        $today = Carbon::today();
        $lastCompletedDate = $settings->last_completed_date;

        if ($lastCompletedDate) {
            // Convert to Carbon date if not already
            $lastDate = Carbon::parse($lastCompletedDate)->startOfDay();
            $diffInDays = $today->diffInDays($lastDate);
            
            if ($diffInDays == 1) {
                // Completed a task on the next consecutive day: increment streak
                $settings->daily_streak += 1;
            } elseif ($diffInDays > 1) {
                // Streak broken, reset to 1
                $settings->daily_streak = 1;
            }
            // If diffInDays is 0, they already completed a task today, so streak is unchanged
        } else {
            // First time completing a task ever
            $settings->daily_streak = 1;
        }

        $settings->last_completed_date = $today;
        $settings->points += 10; // Award 10 points for completing a task
        $settings->save();

        // 2. Check and unlock badges
        $unlockedBadges = $settings->badges;
        if (is_string($unlockedBadges)) {
            $unlockedBadges = json_decode($unlockedBadges, true);
        }
        if (!is_array($unlockedBadges)) {
            $unlockedBadges = [];
        }

        $completedTasksCount = $user->tasks()->where('status', 'Completed')->count();

        $newBadgeUnlocked = false;

        // Check task badges
        if ($completedTasksCount >= 1 && !in_array('first_task', $unlockedBadges)) {
            $unlockedBadges[] = 'first_task';
            $newBadgeUnlocked = true;
            ActivityLog::log($user->id, 'Unlocked Badge: First Steps', 'Completed first task.');
        }
        if ($completedTasksCount >= 10 && !in_array('tasks_10', $unlockedBadges)) {
            $unlockedBadges[] = 'tasks_10';
            $newBadgeUnlocked = true;
            ActivityLog::log($user->id, 'Unlocked Badge: Productivity Padawan', 'Completed 10 tasks.');
        }
        if ($completedTasksCount >= 50 && !in_array('tasks_50', $unlockedBadges)) {
            $unlockedBadges[] = 'tasks_50';
            $newBadgeUnlocked = true;
            ActivityLog::log($user->id, 'Unlocked Badge: Task Master', 'Completed 50 tasks.');
        }

        // Check streak badges
        if ($settings->daily_streak >= 3 && !in_array('streak_3', $unlockedBadges)) {
            $unlockedBadges[] = 'streak_3';
            $newBadgeUnlocked = true;
            ActivityLog::log($user->id, 'Unlocked Badge: Streak Beginner', 'Maintained a 3-day streak.');
        }
        if ($settings->daily_streak >= 7 && !in_array('streak_7', $unlockedBadges)) {
            $unlockedBadges[] = 'streak_7';
            $newBadgeUnlocked = true;
            ActivityLog::log($user->id, 'Unlocked Badge: Streak Champion', 'Maintained a 7-day streak.');
        }

        if ($newBadgeUnlocked) {
            $settings->badges = $unlockedBadges; // Cast handles JSON encoding
            $settings->save();
        }
    }

    /**
     * Check and unlock achievements on other actions.
     */
    public static function checkGoalCreation(User $user)
    {
        $settings = $user->setting;
        if (!$settings) return;

        $unlockedBadges = $settings->badges;
        if (is_string($unlockedBadges)) {
            $unlockedBadges = json_decode($unlockedBadges, true);
        }
        if (!is_array($unlockedBadges)) $unlockedBadges = [];

        if (!in_array('first_goal', $unlockedBadges)) {
            $unlockedBadges[] = 'first_goal';
            $settings->points += 20; // 20 points
            $settings->badges = $unlockedBadges;
            $settings->save();
            ActivityLog::log($user->id, 'Unlocked Badge: Goal Getter', 'Created first goal.');
        }
    }

    public static function checkGoalCompletion(User $user)
    {
        $settings = $user->setting;
        if (!$settings) return;

        $unlockedBadges = $settings->badges;
        if (is_string($unlockedBadges)) {
            $unlockedBadges = json_decode($unlockedBadges, true);
        }
        if (!is_array($unlockedBadges)) $unlockedBadges = [];

        if (!in_array('goal_completed', $unlockedBadges)) {
            $unlockedBadges[] = 'goal_completed';
            $settings->points += 50; // 50 points
            $settings->badges = $unlockedBadges;
            $settings->save();
            ActivityLog::log($user->id, 'Unlocked Badge: Goal Achiever', 'Completed first goal.');
        }
    }

    public static function checkNotesCreation(User $user)
    {
        $settings = $user->setting;
        if (!$settings) return;

        $unlockedBadges = $settings->badges;
        if (is_string($unlockedBadges)) {
            $unlockedBadges = json_decode($unlockedBadges, true);
        }
        if (!is_array($unlockedBadges)) $unlockedBadges = [];

        $notesCount = $user->notes()->count();
        if ($notesCount >= 5 && !in_array('notes_5', $unlockedBadges)) {
            $unlockedBadges[] = 'notes_5';
            $settings->points += 15;
            $settings->badges = $unlockedBadges;
            $settings->save();
            ActivityLog::log($user->id, 'Unlocked Badge: Note Taker', 'Created 5 notes.');
        }
    }
}
