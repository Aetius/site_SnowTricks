<?php


namespace App\Services\Trick;


use App\Entity\Picture;
use App\Entity\Trick;
use App\Form\Trick\DTO\TrickDTO;
use App\Services\TrickGroup\TrickGroupManager;
use App\Services\Upload\Uploader;
use Doctrine\ORM\EntityManagerInterface;

class TrickManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Uploader
     */
    private $uploadService;
    /**
     * @var TrickGroupManager
     */
    private $trickGroupManager;

    public function __construct(EntityManagerInterface $entityManager, Uploader $uploadService,
                                TrickGroupManager $trickGroupManager)
    {
        $this->entityManager = $entityManager;
        $this->uploadService = $uploadService;
        $this->trickGroupManager = $trickGroupManager;
    }

    public function create(TrickDTO $createDTO)
    {
        $trick = new Trick();
        $this->addImage($trick, $createDTO->pictureFiles);
        $trick
            ->setTitle($createDTO->title)
            ->setDescription($createDTO->description)
            ->setTrickGroup($this->trickGroupManager->manager($createDTO));

        $this->entityManager->persist($trick);
        $this->entityManager->flush();

    }

    public function edit(TrickDTO $dto, Trick $trick, array $uploadedFile)
    {
        if ($dto->title) {
            $trick->setTitle($dto->title);
        }
        if ($dto->description) {
            $trick->setDescription($dto->description);
        }
        $this->addImage($trick, $uploadedFile);
        $this->entityManager->persist($trick);
        $this->entityManager->flush();
    }

    public function delete(Trick $trick)
    {
        $this->entityManager->remove($trick);
        $this->entityManager->flush();
    }


    private function addImage(Trick $trick, array $pictures)
    {
        foreach ($pictures as $pictureFile) {
            $picture = new Picture();
            $namePicture = $this->uploadService->uploadTrickImage($pictureFile);
            $picture->setFilename($namePicture);
            $trick->addPicture($picture);
        }

    }
}