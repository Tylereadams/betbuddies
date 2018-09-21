<?php

namespace App\Http\Controllers;

use App\Providers\FullStoryProvider;
use App\Providers\GithubProvider;
use Cache;

class IssuesController extends Controller
{

    public function issues()
    {
        // Cache the data for 5 minutes, github has a rate limit that is easy to hit
        $issueData['issues'] = Cache::remember('issue-data', 5, function () {
            return $this->getIssueData();
        });

        return view('issues', $issueData);
    }

    /**
     * Gathers data for the Issues page.
     * @return array
     */
    private function getIssueData()
    {
        $githubProvider = new GithubProvider();
        $fullStoryProvider = new FullStoryProvider();

        // Get repo issues from github's API
        $issues = $githubProvider->getIssues();

        foreach($issues as $issue){

            // Find the issue creator's email
            $user = $githubProvider->getUser($issue->user->login);

            // If Github didn't return an email from user, try to find it another way.
            if(!$user->email){
                $user->email = $githubProvider->searchForEmail($issue->user->login);
            }

            // Match the issue creator's email to FullStory sessions
            $sessions = $fullStoryProvider->getUserSessions($user->email);

            $issueData[] = [
                'issueData' => $issue,
                'sessions' => $sessions,
                'email' => $user->email ?: ''
            ];
        }

        return $issueData;
    }
}
