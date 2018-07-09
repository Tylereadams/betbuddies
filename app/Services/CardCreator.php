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

        $this->logoOffsetWidth =  intval($this->imageWidth / 10);
        $this->logoOffsetHeigth =  intval($this->imageHeight / 10);

        $this->scoreOffsetHeigth =  (int) $this->imageWidth / 2.3;

        $this->homeScoreOffsetWidth =  900;
        $this->awayScoreOffsetWidth =  300;

        $this->finalOffsetWidth = intval($this->imageWidth / 2);
        $this->finalOffsetHeigth = intval($this->imageHeight / 1.35);

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

        // Home Team Score
        $img->text($this->game->away_score, $this->awayScoreOffsetWidth + 2, $this->scoreOffsetHeigth + 2, function($font) {
            $font->file($this->fontParams['medium']);
            $font->size($this->fontParams['largeSize']);
            $font->align('center');
            $font->color('#ffffff');
        });
        $img->text($this->game->away_score, $this->awayScoreOffsetWidth, $this->scoreOffsetHeigth, function($font) {
            $font->file($this->fontParams['medium']);
            $font->size($this->fontParams['largeSize']);
            $font->align('center');
            $font->color('#000000');
        });

        // Away Team Score
        $img->text($this->game->home_score, $this->homeScoreOffsetWidth + 2, $this->scoreOffsetHeigth + 2, function($font) {
            $font->file($this->fontParams['medium']);
            $font->size($this->fontParams['largeSize']);
            $font->align('center');
            $font->color('#ffffff');
        });
        $img->text($this->game->home_score, $this->homeScoreOffsetWidth, $this->scoreOffsetHeigth, function($font) {
            $font->file($this->fontParams['medium']);
            $font->size($this->fontParams['largeSize']);
            $font->align('center');
            $font->color('#000000');
        });

        // 'Final' text
        $img->text('Final', $this->finalOffsetWidth, $this->finalOffsetHeigth, function($font) {
            $font->file($this->fontParams['light']);
            $font->size($this->fontParams['smallSize']);
            $font->align('center');
            $font->color($this->fontParams['secondaryColor']);
        });

        return $img;
    }
}