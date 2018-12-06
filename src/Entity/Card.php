<?php

namespace App\Entity;

use Symfony\Component\Finder\SplFileInfo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CardRepository")
 */
class Card
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $imageData;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $size;

    public function updateImageData(SplFileInfo $file)
    {
        $this->setName($file->getFilename());
        $this->setImageData($file->getContents());
        $this->setSize($file->getSize());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImageData(): ?string
    {
        if (null !== $this->imageData) {
            return stream_get_contents($this->imageData);
        }

        return null;
    }

    public function setImageData(string $imageData): self
    {
        $this->imageData = base64_encode($imageData);

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }
}
