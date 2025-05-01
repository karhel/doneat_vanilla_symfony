<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * 
 */
class FileUploader
{
    public function __construct(
        private string $targetDirectory,    //< Utilise l'autowiring du fichier de configuration -> voir le fichier config/services.yaml
        private SluggerInterface $slugger,  //< Utilise l'injection de dépendance dans le constructeur du service
        private Filesystem $filesystem,
    )
    { }
    
    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        
        // On gère les exception dans la méthode parente
        $file->move($this->targetDirectory, $newFilename);

        return $newFilename;
    }

    public function uploadContent(string $content, string $filename): string
    {        
        $filePath   = $this->targetDirectory . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($filePath, $content);
        return $filename;
    }

    public function remove(string $filename): void
    {
        $filePath = $this->targetDirectory . DIRECTORY_SEPARATOR . $filename;
        
        if(!file_exists($filePath)) {
            throw new NotFoundHttpException('Image non trouvée');
        }

        $this->filesystem->remove($filePath);
    }

    public function readStream(string $filename): BinaryFileResponse
    {
        $filePath = $this->targetDirectory . DIRECTORY_SEPARATOR . $filename;
        
        if(!file_exists($filePath)) {
            throw new NotFoundHttpException('Image non trouvée');
        }

        $mimeType = mime_content_type($filePath);
        $allowedMimeTypes = ['image/jpeg', 'image/png'];

        if(!in_array($mimeType, $allowedMimeTypes)) {
            throw new FileException('Type de fichier non autorisé');
        }

        $response = new BinaryFileResponse($filePath);

        return $response;
    }

    public function getDirectory()
    {
        return $this->targetDirectory;
    }
}