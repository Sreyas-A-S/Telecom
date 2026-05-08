<?php

namespace App\Policies;

use App\Models\InterviewComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InterviewCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InterviewComment  $interviewComment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, InterviewComment $interviewComment)
    {
        return $user->id === $interviewComment->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InterviewComment  $interviewComment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, InterviewComment $interviewComment)
    {
        return $user->id === $interviewComment->user_id;
    }
}
