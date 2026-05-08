<?php

namespace App\Policies;

class TaskPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->user_id ||
               ($user->employee && $user->employee->id === $task->assigned_to) ||
               ($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager' || $user->employee->role->role === 'Service Manager'));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return ($user->employee && $user->employee->id === $task->assigned_to) ||
               ($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager' || $user->employee->role->role === 'Service Manager'));
    }
}