<?php

namespace App\Services;

use App\Games;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

class CardCreator
{

    public function __construct(Games $game)
    {
        $this->imageHeight = 700;
        $this->imageWidth = 1200;

        $this->logoOffsetWidth =  intval($this->imageWidth / 8);
        $this->logoOffsetHeigth =  intval($this->imageHeight / 10);

        $this->scoreOffsetHeigth =  (int) $this->imageWidth / 2.05;

        $this->homeScoreOffsetWidth =  intval($this->imageWidth / 1.37);
        $this->awayScoreOffsetWidth =  intval($this->imageWidth / 3.75);

        $this->homeRgbColor = hex2rgb($game->homeTeam->colors->first()->hex);
        $this->awayRgbColor = hex2rgb($game->awayTeam->colors->first()->hex);

        $this->fontParams = [
            'smallSize' => 65,
            'largeSize' => 120,
            'medium' => public_path().'/fonts/Roboto-Medium.ttf',
            'light' => public_path().'/fonts/Roboto-Light.ttf',
            'secondaryColor' => '#000000'
        ];

        $this->game = $game;
    }

    public function getGameCard()
    {
        // create a new empty image resource with red background
        $img = Image::canvas($this->imageWidth, $this->imageHeight, '#ffffff');

        // create an image manager instance with favored driver
        $manager = new ImageManager();

        $fill = $manager->make(public_path().$this->game->homeTeam->venue->transparentPhotoUrl())->resize($this->imageWidth, $this->imageHeight);

        $img->fill($fill);

        // to finally create image instances
        $homeImage = $manager->make(public_path().$this->game->homeTeam->logoUrlLarge())
            ->resize(null, 350, function ($constraint) {
                $constraint->aspectRatio();
            });

        $awayImage = $manager->make(public_path().$this->game->awayTeam->logoUrlLarge())
            ->resize(null, 350, function ($constraint) {
                $constraint->aspectRatio();
            });

        $img->insert($awayImage, 'top-left', $this->logoOffsetWidth, $this->logoOffsetHeigth);
        $img->insert($homeImage, 'top-right', $this->logoOffsetWidth, $this->logoOffsetHeigth);

        // define polygon points
        $homePoints = array(
            $this->imageWidth,  $this->scoreOffsetHeigth - 110,  // Point 1 (x, y)
            ($this->imageWidth / 1.92) + 10,  $this->scoreOffsetHeigth - 110, // Point 2 (x, y)
            ($this->imageWidth / 1.92) - 40,  $this->scoreOffsetHeigth + 25,  // Point 3 (x, y)
            $this->imageWidth,  $this->scoreOffsetHeigth + 25,  // Point 4 (x, y)
        );

        // define polygon points
        $awayPoints = array(
            0,  $this->scoreOffsetHeigth - 110,  // Point 1 (x, y)
            ($this->imageWidth / 1.92) - 10,  $this->scoreOffsetHeigth - 110, // Point 2 (x, y)
            ($this->imageWidth / 1.92) - 60,  $this->scoreOffsetHeigth + 25,  // Point 3 (x, y)
            0,  $this->scoreOffsetHeigth + 25,  // Point 4 (x, y)
        );

        // Transparent background for scores
        $img->polygon($homePoints, function ($draw) {
            $draw->background('rgba('.$this->homeRgbColor['red'].', '.$this->homeRgbColor['green'].', '.$this->homeRgbColor['blue'].', 1)');
        });
        $img->polygon($awayPoints, function ($draw) {
            $draw->background('rgba('.$this->awayRgbColor['red'].', '.$this->awayRgbColor['green'].', '.$this->awayRgbColor['blue'].', 1)');
        });

        // Home Team Score
        $img->text($this->game->away_score, $this->awayScoreOffsetWidth + 2, $this->scoreOffsetHeigth + 2, function($font) {
            $font->file($this->fontParams['medium']);
            $font->size($this->fontParams['largeSize']);
            $font->align('center');
            $font->color('#000000');
        });
        // Background Home score
        $img->text($this->game->away_score, $this->awayScoreOffsetWidth, $this->scoreOffsetHeigth, function($font) {
            $font->file($this->fontParams['medium']);
            $font->size($this->fontParams['largeSize']);
            $font->align('center');
            $font->color('#ffffff');
        });

        // Away Team Score
        $img->text($this->game->home_score, $this->homeScoreOffsetWidth + 2, $this->scoreOffsetHeigth + 2, function($font) {
            $font->file($this->fontParams['medium']);
            $font->size($this->fontParams['largeSize']);
            $font->align('center');
            $font->color('#000000');
        });
        // Background Away score
        $img->text($this->game->home_score, $this->homeScoreOffsetWidth, $this->scoreOffsetHeigth, function($font) {
            $font->file($this->fontParams['medium']);
            $font->size($this->fontParams['largeSize']);
            $font->align('center');
            $font->color('#ffffff');
        });


        // 'Final' text
//        $img->text('Final', $this->finalOffsetWidth, $this->finalOffsetHeigth, function($font) {
//            $font->file($this->fontParams['light']);
//            $font->size($this->fontParams['smallSize']);
//            $font->align('center');
//            $font->color($this->fontParams['secondaryColor']);
//        });

        return $img;
    }
}