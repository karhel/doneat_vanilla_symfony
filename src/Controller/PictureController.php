<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PictureController extends AbstractController
{
    #[Route('/meal/{id<\d+>/picture', name: 'app_meal_picture_stream', methods: ['GET'])]
    public function getMealStreamPicture(Meal $meal, FileUploader $fileUploader): Response
    {
        return $fileUploader->readStream($meal->getPicture());
    }
}
