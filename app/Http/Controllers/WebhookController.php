<?php

namespace App\Http\Controllers;

use App\Providers\FullStoryProvider;
use App\Providers\GithubProvider;

class WebhookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function getGithubIssues()
    {

       // $githubProvider = new GithubProvider();
        $fullStoryProvider = new FullStoryProvider();

      //  $issues = $githubProvider->getIssues();

        //foreach($issues as $issue){

            // Find user's email
            //$user = $githubProvider->getUser($issue->user->login);

            $email = 'tyler@gmail.com';
            // Match user email to FullStory sessions
            $sessions = $fullStoryProvider->getUserSessions($email);

            dd($sessions);

            // Edit Github issue with the FullStory url

    //    }

        //dd($issues);

        return true;
    }

}
