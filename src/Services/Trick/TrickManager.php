<?php


namespace App\Services\Trick;


use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\Trick\DTO\TrickDTO;
use App\Services\TrickGroup\TrickGroupManager;
use App\Services\Upload\Uploader;
use App\Services\Video\VideoManager;
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
     * @var VideoManager
     */
    private $videoManager;

    public function __construct(EntityManagerInterface $entityManager, Uploader $uploadService,
                                VideoManager $videoManager)
    {
        $this->entityManager = $entityManager;
        $this->uploadService = $uploadService;
        $this->videoManager = $videoManager;
    }

    public function create(TrickDTO $createDTO)
    {
        $trick = new Trick();
        $this->addImage($trick, $createDTO->pictureFiles);
        $this->addVideo($trick, $createDTO->videos);
        $trick
            ->setTitle($createDTO->title)
            ->setDescription($createDTO->description)
            ->setTrickGroup(($createDTO->trickGroup));
        return $trick;
    }

    public function edit(TrickDTO $dto, Trick $trick, array $uploadedFile)
    {
        if ($dto->title) {
            $trick->setTitle($dto->title);
        }
        if ($dto->description) {
            $trick->setDescription($dto->description);
        }
        if ($dto->trickGroup) {
            $trick->setTrickGroup($dto->trickGroup);
        }
        if ($dto->pictureFiles) {
            $this->addImage($trick, $uploadedFile);
        }
        if ($dto->videos['required'] !== null) {
            $this->addVideo($trick, $dto->videos);
        }
        return $trick;
    }


    public function delete(Trick $trick)
    {
        $this->entityManager->remove($trick);
        $this->entityManager->flush();
    }

    public function save(Trick $trick)
    {
        $this->entityManager->persist($trick);
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

    private function addVideo(Trick $trick, array $videos)
    {
        foreach ($videos as $videoPath) {
            $video = new Video();
            $video->setName($this->videoManager->cleanUrl($videoPath));
            $trick->addVideo($video);
        }
    }
}