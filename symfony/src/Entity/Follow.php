<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'follows')]
#[ORM\UniqueConstraint(columns: ['follower_id', 'following_id'])]
class Follow implements JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'follower_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $follower = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'following_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $following = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?User
    {
        return $this->follower;
    }

    public function setFollower(?User $follower): static
    {
        $this->follower = $follower;

        return $this;
    }

    public function getFollowing(): ?User
    {
        return $this->following;
    }

    public function setFollowing(?User $following): static
    {
        $this->following = $following;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'follower_id' => $this->follower?->getId(),
            'following_id' => $this->following?->getId(),
        ];
    }
}