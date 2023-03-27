<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppService extends AbstractController
{
    public function getHappyMessage(): string
    {
        $messages = [
            'You did it! You updated the system! Amazing!',
            'That was one of the coolest updates I\'ve seen all day!',
            'Great work! Keep going!',
        ];

        $index = array_rand($messages);

        return $messages[$index];
    }

    public function checkUrl()
    {
        $allowUrl = true ;
        if(is_null($this->getUser()))
        {
            $allowUrl = false ;
        }
        return $allowUrl; 
    }

}