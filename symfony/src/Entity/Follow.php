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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getFollower(): ?User
    {
        return $this->follower;
    }

    /**
     * @param User|null $follower
     * @return static
     */
    public function setFollower(?User $follower): static
    {
        $this->follower = $follower;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getFollowing(): ?User
    {
        return $this->following;
    }

    /**
     * @param User|null $following
     * @return static
     */
    public function setFollowing(?User $following): static
    {
        $this->following = $following;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'follower_id' => $this->follower?->getId(),
            'following_id' => $this->following?->getId(),
        ];
    }
}