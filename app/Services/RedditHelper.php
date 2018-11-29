<?php
/**
 * Created by PhpStorm.
 * User: tyler
 * Date: 11/28/18
 * Time: 7:42 PM
 */

namespace App\Services;

class RedditHelper
{
    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getThreadLinks()
    {
        $threadComments = json_decode(file_get_contents($this->url.'?sort=top'));

        //
        $commentsJsonUrl = $threadComments[0]->data->children[0]->data->url.'.json?sort=top';
        $comments = json_decode(file_get_contents($commentsJsonUrl));

        foreach($comments as $commentThread){
            if(!isset($commentThread->data->children[0]->data->replies)){
                continue;
            };
            $i = 1;
            $topLevelComments = $commentThread->data->children[0]->data->replies->data->children;

            foreach($topLevelComments as $comment){
                $highlightData[] = $this->getCommentHighlights($comment);

                if(!isset($comment->data->replies->data->children)){
                    continue;
                }

                foreach($comment->data->replies->data->children as $secondaryComment){
                    if(!$comment->data){
                        continue;
                    }

                    $highlightData[] = $this->getCommentHighlights($secondaryComment);
                }
            }
        }

        return array_filter($highlightData);
    }


    private function getCommentHighlights($comment)
    {
        // Get description
        $descriptionRegex = '/(?<=\[)(.*?)(?=\])/';
        preg_match($descriptionRegex, $comment->data->body, $descriptions);

        // To find the url
        $urlRegex = '/(?<=\()(http.*?)(?=\))/';
        preg_match($urlRegex, $comment->data->body, $urls);

        if(!isset($urls[0])){
            return;
        }

        // Get video src from clippit url
        $clippitHelper = new ClippitHelper($urls[0]);
        $videoSourceUrl = $clippitHelper->getVideoSource();

        //sleep ( 2);
        // Find mp4 in body of reply
        return [
            'description' => isset($descriptions[0]) ? trim($descriptions[0]) : '',
            'url' => $videoSourceUrl
        ];
    }
}